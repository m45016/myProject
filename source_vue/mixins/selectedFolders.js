export const selectedFolders = { // объект избранных папок
  folders: [],
  isEmptyFolders: true,
  isSelectedFolder(path) { // проверка избранна ли папка
    let folders = this.folders;
    for (let folder of folders) {
      if (folder.path.toLocaleLowerCase() === path.toLocaleLowerCase()) {
        return true;
      }
    }
    return false;
  },
  deleteSelectedFolderForPath(pathFolder) { // удаление избранной папки с интерфейса
    if (this.isSelectedFolder(pathFolder)) {

      let folders = this.folders;

      for (let i in folders) {
        if (folders[i].path.toLocaleLowerCase().startsWith(pathFolder.toLocaleLowerCase())) {
          if (!folders.splice(i, 1).length === 0) {
            this.modal.alert('Ошибка: не удалось удалить папку');
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
    for (let folder of folders) {
      if (folder.folder === oldName && folder.path === oldPath) {
        folder.folder = newName;
        folder.path = newPath;
        break;
      }
    }

  }
}

export const selectedFoldersMethods = { // методы избранных папок
  async deleteSelectedFolder(folder) { // удаление избранной папки

    let nameFolder = folder.name;
    let pathFolder = `${folder.path}${nameFolder}/`;

    let json = {
      pathFolder
    }

    json = JSON.stringify(json);

    let url = 'action/deleteSelectedFolder.php';

    try {
      let response = await this.API.send('app', 'deleteSelectedFolder', json);

      if (response !== true) {
        throw new Error('Не корректные данные');
      }

      let folders = this.selectedFolders.folders;
      for (let i in folders) {
        if (folders[i].path.toLocaleLowerCase() === pathFolder.toLocaleLowerCase()) {
          let isDeleted = folders.splice(i, 1).length !== 0;
          if (!isDeleted) {
            throw new Error('Не удалось удалить папку');
          }
        }
      }

      if (folders.length === 0) {
        this.selectedFolders.isEmptyFolders = true;
      }


    } catch (e) {
      await this.modal.alert(`Ошибка удлаения папки из избранного: ${e.message}`);
    }
  },
  async addSelectFolder(folder) { // добавление папки в избранное
    let nameFolder = folder.name;
    let pathFolder = `${folder.path}${nameFolder}/`;

    let json = {
      nameFolder,
      pathFolder
    }

    json = JSON.stringify(json);

    try {

      let response = await this.API.send('app', 'addSelectedFolder', json);

      if (response !== true) {
        throw new Error('Не корректные данные');
      }

      this.selectedFolders.folders.push({
        folder: nameFolder,
        path: pathFolder
      });

      if (this.selectedFolders.isEmptyFolders) {
        this.selectedFolders.isEmptyFolders = false;
      }

    } catch (e) {
      await this.modal.alert(`Ошибка добавления папки в избранное: ${e.message}`);
    }
  }
}