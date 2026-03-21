export const selectedFolders = { // объект избранных папок
  folders: [],
  isEmptyFolders: true,
  isSelectedFolder(path) { // проверка избранна ли папка
    let folders = this.folders;
    for (folder of folders) {
      if (folder.path.toLocaleLowerCase() === path.toLocaleLowerCase()) {
        return true;
      }
    }
    return false;
  },
  deleteSelectedFolderForPath(pathFolder) { // удаление избранной папки с интерфейса
    if (this.isSelectedFolder(pathFolder)) {

      let folders = this.folders;

      for (i in folders) {
        if (folders[i].path.toLocaleLowerCase().startsWith(pathFolder.toLocaleLowerCase())) {
          if (!folders.splice(i, 1).length === 0) {
            alert('Ошибка: не удалось удалить папку');
            return 1;
          }
        }
      }

      if (folders.length === 0) {
        this.isEmptyFolders = true;
      }
    }
  },
  updateNameFolder(oldName, oldPath, newName, newPath) { // обновление имени избранной папки
    let folders = this.folders;
    for (folder of folders) {
      if (folder.folder === oldName && folder.path === oldPath) {
        folder.folder = newName;
        folder.path = newPath;
        break;
      }
    }

  }
}

export const selectedFoldersMethods = { // методы избранных папок
  deleteSelectedFolder(folder) { // удаление избранной папки

    let nameFolder = folder.name;
    let pathFolder = `${folder.path}${nameFolder}/`;

    let form = new FormData();
    form.append('pathFolder', pathFolder);

    let url = 'action/deleteSelectedFolder.php';

    fetch(url, {
      method: 'POST',
      body: form
    })
      .then(response => response.text())
      .then(data => {
        switch (data) {
          case '0':
            let folders = this.selectedFolders.folders;
            for (i in folders) {
              if (folders[i].path.toLocaleLowerCase() === pathFolder.toLocaleLowerCase()) {
                let isDeleted = folders.splice(i, 1).length !== 0;
                if (!isDeleted) {
                  alert('Ошибка: не удалось удалить папку');
                  return 1;
                }
              }
            }

            if (folders.length === 0) {
              this.selectedFolders.isEmptyFolders = true;
            }
            break;
          default:
            alert('Ошибка: не удалось удалить папку');
        }

      }).catch(() => {
        alert("Ошибка подключения к серверу.");
      });
  },
  addSelectFolder(folder) { // добавление папки в избранное
    let nameFolder = folder.name;
    let pathFolder = `${folder.path}${nameFolder}/`;

    let form = new FormData();
    form.append('nameFolder', nameFolder);
    form.append('pathFolder', pathFolder);

    let url = 'action/addSelectedFolder.php';

    fetch(url, {
      method: 'POST',
      body: form
    })
      .then(response => response.text())
      .then(data => {
        switch (data) {
          case '0':
            this.selectedFolders.folders.push({
              folder: nameFolder,
              path: pathFolder
            });

            if (this.selectedFolders.isEmptyFolders) {
              this.selectedFolders.isEmptyFolders = false;
            }

            break;
          default:
            alert('Ошибка: не удалось добавить папку');
        }

      }).catch(() => {
        alert("Ошибка подключения к серверу.");
      });
  }
}