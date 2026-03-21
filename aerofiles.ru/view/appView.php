<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AeroFiles</title>
  <link rel="stylesheet" href="assets/css/app.css">
  <script src="/assets/js/vue/methods/contextMenu.js"></script>
  <script src="/assets/js/vue/methods/documentMethods.js"></script>
  <script src="/assets/js/vue/methods/explorerMethods.js"></script>
  <script src="/assets/js/vue/methods/selectedFolders.js"></script>
</head>
<body>
  <?php require $_SERVER['DOCUMENT_ROOT'] . '/assets/php/header.php' ?>
  <main id='app'>
    <aside class="asideMenu">
      <div class='sizeStorageContainer'>
        <div class='textProgress'>{{freeSize}} свободно из {{maxSize}}</div>
        <progress class='progressSizeStorage' :value="freeSizeInPercent" max='100'></progress>
      </div>
      <div class="asideHeader">Избранное</div>
      <div class="selectedFolders">
        <selected-folders v-for="(selectedFolder, index) in selectedFolders.folders" :selected-folder="selectedFolder" :index="index"></selected-folders>
        <empty-folders v-if="selectedFolders.isEmptyFolders"></empty-folders>
      </div>
    </aside>
    <div class="containerMain">
      <div class="containerWindow">
        <div class='pathContainer'><div class="scrollPathContainer"><span action='goToFolder' path='/'>root</span><span class='path' v-html='path'></span></div></div>
        <div class="storageWindow" ref="storageWindow" :class="{withoutFiles: isEmptyStorage}" @click='clickOnStorage($event)' @dblclick='dblClickOnStorage($event)' @dragover="($e)=>$e.preventDefault()" @drop="uploadFileFromEvent($event)">
          <storage-element v-for="(element, index) in elements" :element='element' :index='index'></storage-element>
          <empty-storage v-if="isEmptyStorage" @create-folder="createFolder"></empty-storage>
        </div>
      </div>
    </div>
    <contextmenu :menu="contextmenu"></contextmenu>
  </main>
  <?php require $_SERVER['DOCUMENT_ROOT'] . '/assets/php/footer.php' ?>
  <script src="/assets/js/vue/vue.global.js"></script>
  <script src="/assets/js/app.js"></script>
</body>
</html>