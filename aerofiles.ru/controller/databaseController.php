<?php

class DataBaseController
{

  private DataBaseModel $model;

  public function __construct(DataBaseModel $model)
  {

    $this->model = $model;
  }

  public function regUser(string $login, string $password, string $email)
  {

    $pass_hash = password_hash($password, PASSWORD_DEFAULT);

    $user = $this->model->createUser($login, $pass_hash, $email);

    if (!$user['isCreated']) {

      return $user['error'];
    }

    return true;
  }

  public function authUser(string $login, string $password)
  {

    $user = $this->model->getUser($login);

    if (!password_verify($password, $user['data']['password'])) {

      $user['error'] = "Пароли не совпадают";
    }

    return $user;
  }

  public function getSelectedFolders(int $idUser)
  {
    $selectedFolders  = $this->model->getSelectedFolder($idUser);

    if (is_null($selectedFolders)) {
      return null;
    }

    $folders = [];

    foreach ($selectedFolders as $selectedFolder) {
      array_push($folders, $selectedFolder);
    }

    return $folders;
  }

  public function isSelectedFolder(int $idUser, string $folder, string $path)
  {
    $selectedFolders = $this->model->getSelectedFolder($idUser);

    if (is_null($selectedFolders)) {
      return false;
    }

    foreach ($selectedFolders as $selectedFolder) {
      if (strtolower($selectedFolder['folder']) === strtolower($folder) && strtolower($selectedFolder['path']) === strtolower($path)) {
        return true;
      }
    }

    return false;
  }

  public function addSelectedFolder(int $idUser, string $folder, string $path)
  {
    $isAdd = $this->model->addSelectedFolder($idUser, $folder, $path);
    return $isAdd;
  }

  public function updateSelectedFolder(int $idUser, string $oldFolder, string $newFolder, string $oldPath, string $newPath)
  {
    $isUpdate = $this->model->updateSelectedFolder($idUser, $oldFolder, $newFolder, $oldPath, $newPath);

    if (!$isUpdate) {
      return null;
    }

    $data = ['oldName' => $oldFolder, 'oldPath' => $oldPath, 'newName' => $newFolder, 'newPath' => $newPath];

    return $data;
  }

  public function updateSelectedFolders(int $idUser, string $oldPath, string $newPath)
  {
    $isUpdate = $this->model->updateSelectedFolders($idUser, $oldPath, $newPath);

    if (!$isUpdate) {
      return null;
    }

    $selectedFolders = $this->getSelectedFolders($idUser);

    return $selectedFolders;
  }
  public function updateNameSelectedFolderForPath(int $idUser, string $oldName, string $newName, string $path){
    $isUpdate = $this->model->updateNameSelectedFolderForPath($idUser, $oldName, $newName, $path);

    return $isUpdate;

  }

  public function deleteSelectedFolder(int $idUser, string $path)
  {
    $isDeleted = $this->model->deleteSelectedFolder($idUser, $path);
    return $isDeleted;
  }

  public function addSizeStorage(int $idUser, int $fileSize)
  {
    $isUpdate = $this->model->addSizeStorage($idUser, $fileSize);

    return $isUpdate;
  }

  public function subSizeStorage(int $idUser, int $fileSize)
  {
    $isUpdate = $this->model->subSizeStorage($idUser, $fileSize);

    return $isUpdate;
  }

  public function getFreeSizeStorage(int $idUser)
  {

    $freeSize = $this->model->getFreeSizeStorage($idUser);

    if (is_null($freeSize)) {
      return null;
    }

    $data = $freeSize->fetch_assoc();

    return $data['freeSizeStorage'];
  }

  public function getMaxSizeStorage(int $idUser)
  {
    $maxSize = $this->model->getMaxSizeStorage($idUser);

    if (is_null($maxSize)) {
      return null;
    }

    $data = $maxSize->fetch_assoc();

    return $data['maxSizeStorage'];
  }

  public function getFreeSizeStorageInPercent(int $idUser)
  {
    $freeSize = $this->model->getFreeSizeInPercent($idUser);

    if (is_null($freeSize)) {
      return null;
    }

    $data = $freeSize->fetch_assoc();

    return $data['freeSizeStorageInPercent'];
  }

  public function getAllUsers()
  {
    $dataUsers = $this->model->getAllUsers();

    if (is_null($dataUsers)) {
      return null;
    }

    $users = [];

    foreach ($dataUsers as $user) {
      $freeSize = $this->getFreeSizeStorage($user['id_user']);
      $freeSizeInPercent = $this->getFreeSizeStorageInPercent($user['id_user']);
      $user['freeSize'] = $freeSize;
      $user['freeSizeInPercent'] = $freeSizeInPercent;
      array_push($users, $user);
    }

    return $users;
  }

  public function getUserOnLogin(string $login)
  { 
    $user =  $this->model->getUser($login);

    if(!is_null($user['error'])){
      return $user;
    }

    $freeSize = $this->getFreeSizeStorage($user['data']['id_user']);
    $freeSizeInPercent = $this->getFreeSizeStorageInPercent($user['data']['id_user']);
    $userp['data']['password'] = null;
    $user['data']['freeSize'] = $freeSize;
    $user['data']['freeSizeInPercent'] = $freeSizeInPercent;
    return $user;
  }
  public function updateUserData($idUser, $maxStorage, $isAdmin)
  {
    $isUpdate = $this->model->updateUserData($idUser, $maxStorage, $isAdmin);

    return $isUpdate;
  }

  public function resetPasswordUser($email, $password)
  {
    $isReset = $this->model->resetPasswordUser($email, $password);

    return $isReset;
  }

  public function close()
  {
    $this->model->close();
  }
}
