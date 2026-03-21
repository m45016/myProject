const APP = Vue.createApp({ // создание приложения на Vue
  data() {
    return {
      isEmptyStorage: true,
      path: '/',
      elements: [],
      freeSize: 'None',
      freeSizeInPercent: '0',
      maxSize: 'None',
      countFiles: 0,
      progressNumber: 1,
      pathUser: '/',
      buffer: null,
      renameMode: false,
      forbiddenChars: ['/', '\\', '?', '*', ':', '"', '<', '>', '|'],
      editElement: null,
      editName: null,
      contextmenu: { // контекстного меню
        ...contextMenu
      },
      selectedFolders: { // избранные папки 
        ...selectedFolders
      }
    }
  },
  created() { // получение данных с сервера
    fetch('/action/openRoot.php')
      .then(response => response.json())
      .then(data => {
        console.log(data);
        this.isEmptyStorage = data.emptyStorage;
        this.elements = data.elements;
        this.freeSize = data.freeSize;
        this.freeSizeInPercent = data.freeSizeInPercent;
        this.maxSize = data.maxSize;
        this.pathUser = data.pathUser;
        this.countFiles = data.countFiles;
        if (data.selectedFolders !== null) {
          this.selectedFolders.folders = data.selectedFolders;
          this.selectedFolders.isEmptyFolders = false;
        }
      });
  },
  mounted() { // установка событии на документ
    document.addEventListener('contextmenu', this.openContextMenu);
    document.addEventListener('click', this.clickOnDocument);
    window.addEventListener('resize', this.hideContextMenu);
    document.addEventListener('keydown', this.keyDown);
  },
  beforeUnmount() { // удаление событии с документа при закрытии приложения
    document.removeEventListener('contextmenu', this.openContextMenu);
    document.removeEventListener('click', this.clickOnDocument);
    window.removeEventListener('resize', this.hideContextMenu);
    document.removeEventListener('keydown', this.keyDown)
  },
  provide() { // публикуем методы, чтобы не протаскивать события в глубоко вложенные компоненты
    return {
      'MENU': {
        contextmenu: this.contextmenu,
        createFolder: this.createFolder,
        addSelectFolder: this.addSelectFolder,
        getFirstActiveElement: this.getFirstActiveElement,
        deleteSelectedFolder: this.deleteSelectedFolder,
        setModeRenameFile: this.setModeRenameFile,
        deleteFiles: this.deleteFiles,
        getActiveElements: this.getActiveElements,
        openFolder: this.openFolder,
        downloadFiles: this.downloadFiles,
        copyFiles: this.copyFiles,
        cutFiles: this.cutFiles,
        pasteFiles: this.pasteFiles,
        abortLoading: this.abortLoading,
        createInputFile: this.createInputFile,
        uploadFileFromInput: this.uploadFileFromInput
      }
    }
  },
  methods: { // методы приложения
    ...explorerMethods,
    ...selectedFoldersMethods,
    ...contextmenuMethods,
    ...documentMethods
  }
}) // создание компонентов
  .component('empty-storage', { 
    emits: ['create-folder'],
    template: `<div class='text-center'>Переместите файлы или создайте папку</div>
            <div>
              <button class="btn" @click="createFolder()">Создать папку</button>
            </div>`,
    methods: {
      createFolder() {
        this.$emit('create-folder');
      }
    }
  })
  .component('storage-element', {
    props: ['element', 'index'],
    template: `<folder v-if="!element['isFile']" :data="element" :index="index"></folder>
                  <file v-else :data="element" :index="index"></file>`
  })
  .component('folder', {
    props: ['data', 'index'],
    template: `<div class="storageElement __folder__" :path="data['path']" :title="generateTitle()">
                  <div class="wrapperElement __folder__" :class="{activeElement: data['isActive']}" :index='index'>
                    <div class="imgBlock __folder__"><img :src="generateSrc()" alt="folder" class="imgElement __folder__"></div>
                    <div class="nameElement __folder__" :class="{editNameElement: data['isEditName']}" :contenteditable="data['isEditName']">{{data['name']}}</div>
                  </div>
                </div>`,
    methods: {
      generateTitle() {
        return `Тип: ${this.data['dataType']['title']}\nДата создания: ${this.data['ctime']}`;
      },
      generateSrc() {
        return `assets/img/${this.data['dataType']['type']}.svg`;
      }
    }
  })
  .component('file', {
    props: ['data', 'index'],
    template: `<div class="storageElement __file__" :path="data['path']" :typeFile="generateType()" :title="generateTitle()">
                  <div class="wrapperElement __file__" :class="{activeElement: data['isActive']}" :index='index'>
                    <div class="imgBlock __file__"><img :src="generateSrc()" alt="file" class="imgElement __file__"></div>
                    <div class="nameElement __file__" :class="{editNameElement: data['isEditName']}" :contenteditable="data['isEditName']">{{data['name']}}</div>
                  </div>
                  <div class="progressZone" v-if="data['isLoading']">
                    <progress class="progressLoaded" :value="data['progressLoad']" max="100"></progress><div><span class="progressLoad">{{data['progressLoad']}}</span>%/100%</div>
                  </div>
                </div>`,
    methods: {
      generateTitle() {
        return `Тип: ${this.data['dataType']['title']}\nРазмер файла: ${this.data['formatedSize']}\nДата загрузки: ${this.data['ctime']}`;
      },
      generateSrc() {
        return `assets/img/file${this.data['dataType']['img']}.svg`;
      },
      generateType() {
        return `.${this.data['dataType']['type']}`;
      }
    }
  })
  .component('contextmenu', {
    props: ['menu'],
    template: `<div class="menu __menuTag__" :class="{hidden: menu['isHidden']}" :ref="(el)=>menu['menu']=el">
                    <div class="contextMenu __menuTag__">
                      <contextmenu-action-group v-for="(group, nameGroup) in menu['groups']" :group="group" :nameGroup="nameGroup"></contextmenu-action-group>
                    </div>
                  </div>`
  }).component('contextmenu-action-group', {
    props: ['group', 'nameGroup'],
    template: `<div class="groupActions __menuTag__" :class="{hidden: group.isHidden}" :nameGroup="nameGroup">
                    <contextmenu-action v-for="action in group['actions']" :action='action'></contextmenu-action>
                  </div>`,
  }).component('contextmenu-action', {
    props: ['action'],
    template: `<div class="action __actionMenu__" :class="{hidden: action['isHidden']}" :action="action['action']" @click="click($event)">{{action['nameAction']}} <kbd v-if="action['isExistKBD']">{{action['kbd']}}</kbd></div>`,
    inject: ['MENU'], // разрешаем доступ к опубликованным методам приложения
    methods: {
      click(e) {
        let functionName = e.target.getAttribute('action');
        switch (functionName) {
          case 'createFolder':
            this.MENU.createFolder();
            break;
          case 'addSelectFolder': {
            let folder = this.MENU.getFirstActiveElement();
            this.MENU.addSelectFolder(folder);
            break;
          }
          case 'deleteSelectedFolder': {
            let folder = this.MENU.getFirstActiveElement();
            this.MENU.deleteSelectedFolder(folder);
            break;
          }
          case 'rename': {
            let file = this.MENU.getFirstActiveElement();
            this.MENU.setModeRenameFile(file);
            break;
          }
          case 'delete': {
            let confirmDeleteFiles = confirm('Вы точно хотите удалить выделенные файлы?');
            if (confirmDeleteFiles) {
              let files = this.MENU.getActiveElements();
              this.MENU.deleteFiles(files);
            }
            break;
          }
          case 'open': {
            let folder = this.MENU.getFirstActiveElement();
            this.MENU.openFolder(folder);
            break;
          }
          case 'download': {
            let files = this.MENU.getActiveElements();
            this.MENU.downloadFiles(files);
            break;
          }
          case 'copy': {
            let files = this.MENU.getActiveElements();
            this.MENU.copyFiles(files);
            break;
          }
          case 'cut': {
            let files = this.MENU.getActiveElements();
            this.MENU.cutFiles(files);
            break;
          }
          case 'paste': {
            this.MENU.pasteFiles();
            break;
          }
          case 'abortLoading': {
            let file = this.MENU.getFirstActiveElement();
            this.MENU.abortLoading(file);
            break;
          }
          case 'upload':{
            let file = document.querySelector("[type='file']");
            if(!file){
              file = this.MENU.createInputFile();
              file.addEventListener('change',this.MENU.uploadFileFromInput.bind(null, file));
            }
            file.click();
          }
        }
        this.MENU.contextmenu.isHidden = true;
      }
    }
  }).component('selected-folders', {
    props: ['selectedFolder', 'index'],
    template: `<div class="selectedFolder" :path='selectedFolder.path'>{{selectedFolder.folder}}</div>`
  }).component('empty-folders', {
    template: `<div class="withoutFolders">У вас нет избранных папок</div>`
  }).mount('#app'); // запуск приложения.