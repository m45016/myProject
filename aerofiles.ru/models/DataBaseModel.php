<?php

require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/errors/databaseError.php";

class DataBaseModel
{

  private mysqli $mysql; // база данных
  private array $sizesStorage = [ // типы размеров хранилища
    '5 МБ' => 5242880,
    '10 МБ' => 10485760,
    '50 МБ' => 52428800,
    '100 МБ' => 104857600,
    '500 МБ' => 524288000,
    '10 ГБ' => 10737418240,
    '25 ГБ' => 26843545600,
    '50 ГБ' => 53687091200,
    '75 ГБ' => 80530636800,
    '100 ГБ' => 107374182400,
    '250 ГБ' => 268435456000,
    '500 ГБ' => 536870912000,
    '750 ГБ' => 805306368000,
    '1 ТБ' => 1099511627776
  ];

  public function __construct(string $domain, string $user, string $password, string $db_name)
  {
    $mysql = new mysqli($domain, $user, $password, $db_name);

    if ($mysql->connect_error) {
      throw new DataBaseError("База данных не найдена");
    }

    $this->mysql = $mysql;
  }
  // Регистрация пользователя
  public function createUser(string $login, string $password, string $email):bool
  {

    $sql = "INSERT INTO `user` (`login`,`password`, `email`) VALUES (?,?,?);";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('sss', $login, $password, $email);
    $query->execute();

    if ($query->affected_rows <= 0) {
      throw new DataBaseError($query->error);
    }

    $query->close();
    return true;
  }
  // Получение пользователя по логину
  public function getUser(string $login): object
  {

    $sql = "SELECT * FROM `user` WHERE `login` = ?;";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('s', $login);
    $query->execute();

    $result = $query->get_result();

    $query->close();

    return $result;
  }
  // Получение пользователя по ID
  public function getUserById(int $idUser): object
  {

    $sql = "SELECT * FROM `user` WHERE `id_user` = ?";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('i', $idUser);
    $query->execute();

    $result = $query->get_result();

    $query->close();

    return $result;
  }
  // Сброс пароля пользователя 
  public function resetPasswordUser(string $email, string $password):int
  {
    $pass_hash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "UPDATE `user` SET `password` = ? WHERE `email` = ?;";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('ss', $pass_hash, $email);
    $query->execute();
    return $query->affected_rows;
  }

  // Добавление папки в избранное
  public function addSelectedFolder(int $idUser, string $folder, string $path): bool
  {

    $sql = "INSERT INTO `selectedFolder` (`user`, `folder`, `path`) VALUES (?,?,?)";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('iss', $idUser, $folder, $path);
    $query->execute();

    if ($query->affected_rows <= 0) {
      throw new DataBaseError('Невозможно добавить папку для несуществующего пользователя');
    }

    $query->close();
    return true;
  }

  // Получение избранных папок пользователя
  public function getSelectedFolders(int $idUser): object
  {

    $sql = "SELECT `folder`, `path` FROM `selectedFolder` WHERE `user`  = ?";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('i', $idUser);
    $query->execute();

    $result = $query->get_result();
    $query->close();

    return $result;
  }

  // Обновление данных избранной папки
  public function updateSelectedFolder(int $idUser, string $oldName, string $newName, string $oldPath, string $newPath):int
  {

    $sql = "UPDATE `selectedFolder` SET `folder` = ?, `path` = ? WHERE `user` = ? AND `folder` = ? AND `path` = ?";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('ssiss', $newName, $newPath, $idUser, $oldName, $oldPath);
    $query->execute();

    return $query->affected_rows;
  }

  // Обновление пути избраных папок
  public function updatePathSelectedFolders(int $idUser, string $oldPath, string $newPath):int
  {
    $sql = "UPDATE `selectedFolder` SET `path` = REPLACE(`path`, ?, ?) WHERE `user` = ? AND `path` LIKE ?;";
    
    $likeSQL = "{$oldPath}%";
    $query = $this->mysql->prepare($sql);
    $query->bind_param('ssis', $oldPath, $newPath, $idUser, $likeSQL);
    $query->execute();

    return $query->affected_rows;

  }

  // обновлние имени избранной папки по пути
  public function updateNameSelectedFolderForPath(int $idUser, string $oldName, string $newName, string $path):int
  {
    $sql = "UPDATE `selectedFolder` SET `folder` = ? WHERE `user` = ? AND `path` = ? AND `folder` = ?";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('siss', $newName, $idUser, $path, $oldName);
    $query->execute();

    return $query->affected_rows;
  }

  // Удаление папки из избранного
  public function deleteSelectedFolder(int $idUser, string $path):int
  {
    $sql = "DELETE FROM `selectedFolder` WHERE `user` = ?  AND `path` = ?";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('is', $idUser, $path);
    $query->execute();

    return $query->affected_rows;
  }

  // Добавление размера хранилищу пользователя
  public function addSizeStorage(int $idUser, int $fileSize): bool
  {
    $sql = "UPDATE `user` SET `sizeStorage` = `sizeStorage` + ? WHERE `id_user` = ? AND `sizeStorage` + ? <= `maxSizeStorage`;";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('iii', $fileSize, $idUser, $fileSize);
    $query->execute();

    if ($query->affected_rows <= 0) {
      throw new DataBaseError('Недостаточно места в хранилище :(');
    }

    $query->close();
    return true;
  }

  // Вычитание размера хранилища пользователя
  public function subSizeStorage(int $idUser, int $fileSize): bool
  {
    $sql = "UPDATE `user` SET `sizeStorage` = `sizeStorage` - ? WHERE `id_user` = ? AND `sizeStorage` - ? >= 0;";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('iii', $fileSize, $idUser, $fileSize);
    $query->execute();

    if ($query->affected_rows < 0) {
      throw new DataBaseError('Размер хранилище не может быть отрицательным');
    }

    $query->close();
    return true;
  }

  // Получение данных о хранилище пользователя
  public function getStorageInfo(int $idUser):object
  {
    $sql = "SELECT `maxSizeStorage` - `sizeStorage` as `freeSizeStorage`, `maxSizeStorage`, `sizeStorage` / `maxSizeStorage` * 100 as `freeSizeStorageInPercent` FROM `user` WHERE `id_user` = ?;";
    $query = $this->mysql->prepare($sql);
    $query->bind_param('i', $idUser);
    $query->execute();

    $result = $query->get_result();
    $query->close();

    return $result;
  }

  // Обновление данных пользователя
  public function updateUserData(int $idUser, string $maxStorage, int $isAdmin):int
  {
    $user = $this->getUserById($idUser);

    if($user->num_rows === 0){
      return 0;
    }
    
    $user = $user->fetch_assoc();

    if($maxStorage === 'No Change'){
      $maxStorage = $user['maxSizeStorage'];
    }
    else{
      $maxStorage = $this->sizesStorage[$maxStorage];
    }

    if(gettype($maxStorage) !== 'integer' || $isAdmin > 1 || $isAdmin < 0){
      return 0;
    }
    
    $sql = 'UPDATE `user` SET `maxSizeStorage` = ?, `isAdmin` = ? WHERE `id_user` = ?;';

    $query = $this->mysql->prepare($sql);
    $query->bind_param('iii', $maxStorage, $isAdmin, $idUser);
    $query->execute();

    return $query->affected_rows;

  }

  // Закрытие БД
  public function close():void
  {
    $this->mysql->close();
  }
}
