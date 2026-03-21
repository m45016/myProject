<template>
  <div class="action __actionMenu__" :class="{hidden: action['isHidden']}" :action="action['action']" @click="click($event)">{{action['nameAction']}} <kbd v-if="action['isExistKBD']">{{action['kbd']}}</kbd></div>
</template>

<script>

export default {
  props: ['action'],
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
}

</script>