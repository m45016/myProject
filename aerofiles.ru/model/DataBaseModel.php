<?php

class DataBaseModel{

  private mysqli $mysql;
  private array $sizesStorage = [
    '5 МБ'=>5242880,
    '10 МБ'=>10485760,
    '50 МБ'=>52428800,
    '100 МБ'=>104857600,
    '500 МБ'=>524288000,
    '10 ГБ'=>10737418240,
    '25 ГБ'=>26843545600,
    '50 ГБ'=>53687091200,
    '75 ГБ'=>80530636800,
    '100 ГБ'=>107374182400,
    '250 ГБ'=>268435456000,
    '500 ГБ'=>536870912000,
    '750 ГБ'=>805306368000,
    '1 ТБ'=>1099511627776
  ];

  public function __construct(string $domen, string $user, string $password, string $db_name){
    $mysql = new mysqli($domen, $user, $password, $db_name);

    if ($mysql->connect_error) {
      exit("Ошибка: {$mysql->connect_error}");
    }

    $this->mysql = $mysql;
  }

  public function createUser(string $login, string $password, string $email){

    $sql = "INSERT INTO `user` (`login`,`password`, `email`) VALUES (?,?,?);";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('sss', $login, $password, $email);
    $query->execute();

    $user = ['isCreated'=>false, 'error'=>""];

    if ($query->affected_rows > 0) {
      $user['isCreated'] = true;
    }
    else{
      $user['error'] = $query->error;
    }

    $query->close();

    return $user;
  }

  public function getUser(string $login){

    $sql = "SELECT * FROM `user` WHERE `login` = ?;";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('s', $login);
    $query->execute();

    $result = $query->get_result();

    $query->close();

    $user = ['data'=>null,'error'=>null];

    if($result->num_rows === 0){

      $user['error'] = "Пользователь не найден";
      return $user;

    }

    $user['data'] = $result->fetch_assoc();

    return $user;

  }

  public function getSelectedFolder(int $idUser){

    $sql = "SELECT `folder`, `path` FROM `selectedFolder` WHERE `user`  = ?";
    
    $query = $this->mysql->prepare($sql);
    $query->bind_param('i', $idUser);
    $query->execute();

    $result = $query->get_result();
    $query->close();

    if($result->num_rows === 0){
      return null;
    }

    return $result;

  }

  public function addSelectedFolder(int $idUser, string $folder, string $path){
    $sql = "INSERT INTO `selectedFolder` (`user`, `folder`, `path`) VALUES (?,?,?)";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('iss', $idUser, $folder, $path);
    $query->execute();

    $isAdd = false;

    if($query->affected_rows > 0){
      $isAdd = true;
    }

    $query->close();

    return $isAdd;

  }

  public function updateSelectedFolder(int $idUser, string $oldFolder, string $newFolder, string $oldPath, string $newPath){

    $sql = "UPDATE `selectedFolder` SET `folder` = ?, `path` = ? WHERE `user` = ? AND `folder` = ? AND `path` = ?";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('ssiss', $newFolder, $newPath, $idUser, $oldFolder, $oldPath);
    $query->execute();

    $isUpdate = false;

    if($query->affected_rows > 0){
      $isUpdate = true;
    }

    $query->close();

    return $isUpdate;

  }
  public function updateSelectedFolders(int $idUser, string $oldPath, string $newPath){
    $sql = "UPDATE `selectedFolder` SET `path` = REPLACE(`path`, ?, ?) WHERE `user` = ? AND `path` LIKE ?;";

    $likeSQL = "{$oldPath}%";
    $query = $this->mysql->prepare($sql);
    $query->bind_param('ssis', $oldPath, $newPath, $idUser, $likeSQL);
    $query->execute();

    $isUpdate = false;

    if($query->affected_rows > 0){
      $isUpdate = true;
    }

    $query->close();

    return $isUpdate;
  }
  public function updateNameSelectedFolderForPath(int $idUser, string $oldName, string $newName, string $path){
    $sql = "UPDATE `selectedFolder` SET `folder` = ? WHERE `user` = ? AND `path` = ? AND `folder` = ?";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('siss', $newName, $idUser, $path, $oldName);
    $query->execute();

    $isUpdate = false;

    if($query->affected_rows > 0){
      $isUpdate = true;
    }

    $query->close();

    return $isUpdate;
  }
  public function deleteSelectedFolder(int $idUser, string $path){
    $sql = "DELETE FROM `selectedFolder` WHERE `user` = ?  AND `path` = ?";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('is', $idUser, $path);
    $query->execute();

    $isDeleted = false;

    if($query->affected_rows > 0){
      $isDeleted = true;
    }

    $query->close();

    return $isDeleted;

  }

  public function addSizeStorage(int $idUser, int $fileSize){
    $sql = "UPDATE `user` SET `sizeStorage` = `sizeStorage` + ? WHERE `id_user` = ? AND `sizeStorage` + ? <= `maxSizeStorage`;";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('iii', $fileSize, $idUser, $fileSize);
    $query->execute();

    $isUpdate = false;

    if($query->affected_rows > 0){
      $isUpdate = true;
    }

    $query->close();

    return $isUpdate;

  }

  public function subSizeStorage(int $idUser, int $fileSize){
    $sql = "UPDATE `user` SET `sizeStorage` = `sizeStorage` - ? WHERE `id_user` = ? AND `sizeStorage` - ? >= 0;";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('iii', $fileSize, $idUser, $fileSize);
    $query->execute();

    $isUpdate = false;

    if($query->affected_rows > 0){
      $isUpdate = true;
    }

    $query->close();

    return $isUpdate;

  }

  public function getFreeSizeStorage(int $idUser){

    $sql = "SELECT `maxSizeStorage` - `sizeStorage` as `freeSizeStorage` FROM `user` WHERE `id_user` = ?;";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('i', $idUser);
    $query->execute();

    $result = $query->get_result();
    $query->close();

    if($result->num_rows === 0){
      return null;
    }

    return $result;

  }

  public function getMaxSizeStorage(int $idUser){

    $sql = "SELECT `maxSizeStorage` FROM `user` WHERE `id_user` = ?;";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('i', $idUser);
    $query->execute();

    $result = $query->get_result();
    $query->close();

    if($result->num_rows === 0){
      return null;
    }

    return $result;

  }

  public function getFreeSizeInPercent(int $idUser){

    $sql = "SELECT `sizeStorage` / `maxSizeStorage` * 100 as `freeSizeStorageInPercent` FROM `user` WHERE `id_user` = ?;";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('i', $idUser);
    $query->execute();

    $result = $query->get_result();
    $query->close();

    if($result->num_rows === 0){
      return null;
    }

    return $result;

  }

  public function getAllUsers(){
    $sql = "SELECT `id_user`, `login`, `email`,`isAdmin`,`sizeStorage`,`maxSizeStorage` FROM `user`;";

    $query = $this->mysql->query($sql);
    
    if($query->num_rows === 0){
      return null;
    }

    return $query;

  }

  public function getUserOnID($idUser){

    $sql = "SELECT * FROM `user` WHERE `id_user` = ?";
    
    $query = $this->mysql->prepare($sql);
    $query->bind_param('s',$idUser);
    $query->execute();
    
    $result = $query->get_result();
    $query->close();

    if($result->num_rows === 0){
      return null;
    }

    return $result->fetch_assoc();

  }

  public function updateUserData($idUser, $maxStorage, $isAdmin){
    $user = $this->getUserOnID($idUser);

    $isUpdate = false;

    if(is_null($user)){
      return $isUpdate;
    }
    else if(($maxStorage === 'No Change' || $this->sizesStorage[$maxStorage] === $user['maxSizeStorage']) && $isAdmin != $user['isAdmin']){
      $sql = 'UPDATE `user` SET `isAdmin` = ? WHERE `id_user` = ?;';

      $query = $this->mysql->prepare($sql);
      $query->bind_param('is',$isAdmin, $idUser);
      $query->execute();

      if($query->affected_rows > 0){
        $isUpdate = true;
      }

      $query->close();

    }
    else if(($maxStorage !== 'No Change' && $this->sizesStorage[$maxStorage] !== $user['maxSizeStorage']) && $isAdmin == $user['isAdmin']){
      $sql = 'UPDATE `user` SET `maxSizeStorage` = ? WHERE `id_user` = ?;';

      $query = $this->mysql->prepare($sql);
      $query->bind_param('is', $this->sizesStorage[$maxStorage], $idUser);
      $query->execute();

      if($query->affected_rows > 0){
        $isUpdate = true;
      }

      $query->close();
    }
    else if(($maxStorage !== 'No Change' && $this->sizesStorage[$maxStorage] !== $user['maxSizeStorage']) && $isAdmin != $user['isAdmin']){
      $sql = 'UPDATE `user` SET `maxSizeStorage` = ?, `isAdmin` = ? WHERE `id_user` = ?;';
      
      $query = $this->mysql->prepare($sql);
      $query->bind_param('iis',$this->sizesStorage[$maxStorage], $isAdmin, $idUser);
      $query->execute();

      if($query->affected_rows > 0){
        $isUpdate = true;
      }

      $query->close();
    }

    return $isUpdate;

  }

  public function resetPasswordUser($email, $password){
    $isReset = false;

    $pass_hash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "UPDATE `user` SET `password` = ? WHERE `email` = ?;";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('ss',$pass_hash,$email);
    $query->execute();
    
    if($query->affected_rows > 0){
      $isReset = true;
    }

    $query->close();

    return $isReset;

  }

  public function close(){

    $this->mysql->close();

  }
  
}
