<?php

class ExplorerController
{

  private ExplorerModel $explorer;
  private int $numberFolder = 0;
  private string $defaultNameFolder = 'Новая папка';

  public function __construct(ExplorerModel $explorer)
  {

    $this->explorer = $explorer;
  }

  public function createStorage(string $nameStorage)
  {

    $isCreated = $this->explorer->createFolder($nameStorage);

    return $isCreated;
  }

  public function createFolder(string $nameFolder = 'Новая папка')
  {

    $dataFolder = ['data' => null, 'status' => null];

    $isCreated = $this->explorer->createFolder($nameFolder);

    for ($i = 0; $i < 9999; $i++) {

      if ($isCreated) {
        break;
      }

      $this->numberFolder++;
      $nameFolder = "{$this->defaultNameFolder} ({$this->numberFolder})";
      $isCreated = $this->explorer->createFolder($nameFolder);
    }

    if ($this->numberFolder >= 9998) {
      return 1;
    }

    $folderStat = $this->getStatFile($nameFolder);
    $dataFolder['data'] = $folderStat;
    $dataFolder['status'] = $isCreated;
    return $dataFolder;
  }

  public function getFilesFromCurrentPath()
  {

    $dataFiles = $this->explorer->getFilesFromCurrentPath();

    $pathUser = $this->getPathUser();

    $contentCurrentPath = [
      'elements' => [],
      'length' => 0,
      'path' => $pathUser
    ];

    $countElements = 0;
    $files = [];
    $folders = [];

    foreach ($dataFiles['files'] as $file) {
      $element = [];

      $typeFile = $this->explorer->getTypeFile($file->getFilename());

      $element['ctime'] = $this->explorer->getDataFromTimeStamp($file->getCTime());
      $element['path'] = $pathUser;
      $element['isActive'] = false;
      $element['isEditName'] = false;
      $element['isLoading'] = false;

      if ($file->isFile()) {
        $typeFile = $this->explorer->getTypeFile($file->getFilename());

        $element['isFile'] = true;
        $element['name'] = $this->explorer->getShortNameFile($file->getFilename());
        $element['dataType']['type'] = $typeFile;
        $element['dataType']['title'] = $this->explorer->getTitleFile($typeFile);
        $element['dataType']['img'] = strtoupper($this->explorer->getTypeFileForImg($typeFile));
        $element['fullsize'] = $file->getSize();
        $element['formatedSize'] = $this->explorer->formatSizeFile($file->getSize());

        array_push($files, $element);
      } else {

        $element['isFile'] = false;
        $element['name'] = $file->getFilename();
        $element['dataType']['type'] = 'Folder';
        $element['dataType']['title'] = 'Папка';
        $element['size'] = "";

        array_push($folders, $element);
      }
      $countElements++;
    }

    $contentCurrentPath['elements'] = array_merge($folders, $files);

    $contentCurrentPath['length'] = $countElements;

    return $contentCurrentPath;
  }

  public function is_file($element)
  {
    return $element['isFile'];
  }

  public function uploadFile($file)
  {
    $isUpload = $this->explorer->uploadFile($file);
    return $isUpload;
  }

  public function deleteFile($filePath, $isFullPath = false)
  {
    $isDeleted = $this->explorer->deleteFile($filePath, $isFullPath);
    return $isDeleted;
  }

  public function deleteFolder($folderPath, $isFullPath = false)
  {
    $isDeleted = $this->explorer->deleteFolder($folderPath, $isFullPath);
    return $isDeleted;
  }

  public function getStatFile($fileName, $isFullPath = false)
  {
    $element = $this->explorer->getStatFile($fileName, $isFullPath);
    $element['ctime'] = $this->explorer->getDataFromTimeStamp($element['ctime']);
    $element['path'] = $this->getPathUser();
    $element['isActive'] = false;
    $element['isEditName'] = false;
    $element['isLoading'] = false;
    return $element;
  }

  public function openFolder($path)
  {
    $this->explorer->openFolder($path);
  }

  public function getPathUser()
  {
    return $this->explorer->getPathUser();
  }

  public function getPathStorage()
  {
    return $this->explorer->getPathStorage();
  }

  public function renameFile($oldName, $newName)
  {

    if ($oldName === $newName) {
      return false;
    }

    $isRenamed = $this->explorer->renameFile($oldName, $newName);
    if (!$isRenamed) {
      return false;
    }
    return true;
  }

  public function genToken()
  {

    $lenToken = random_int(10, 20);
    $token = $this->explorer->genToken($lenToken);

    return $token;
  }

  public function downloadFile(string $fileName)
  {

    $filePath = "{$this->getPathStorage()}{$this->getPathUser()}{$fileName}";

    if (!is_file($filePath)) {
      return false;
    } else if (!isset($_POST['t'])) {
      $token = $this->genToken();
      $_SESSION['t'] = $token;
      return $token;
    }

    $token = $_POST['t'];

    if ($token === $_SESSION['t']) {
      $this->explorer->downloadFile($filePath);
    }
  }

  public function copyFile($oldPath, $newPath, $file): array
  {
    $data = ['file' => [], 'status' => false, 'sizeFile' => 0];

    $oldPathUserFile = "{$oldPath}{$file[0]}{$file[1]}";
    $oldPathFile = "{$this->getPathStorage()}{$oldPathUserFile}";
    

    if (!file_exists($oldPathFile)) {
      return $data;
    }
    $newPathUserFile = "{$newPath}{$file[0]}{$file[1]}";
    $newPathFile = "{$this->getPathStorage()}{$newPathUserFile}";
    
    if (is_dir($oldPathFile)) {

      $isSelfCopy = $this->isSelfCopy($oldPathFile, $newPathFile);

      if ($isSelfCopy) {
        $data['isSelfCopy'] = true;
        return $data;
      }

      if (is_dir($newPathFile)) {
        for ($i = 1; $i < 9999; $i++) {
          $newPathUserFile = "{$newPath}{$file[0]} Копия({$i}){$file[1]}";
          $newPathFile = "{$this->getPathStorage()}{$newPathUserFile}";
          if (!is_dir($newPathFile)) {
            break;
          } else if ($i >= 9998) {
            return $data;
          }
        }
      }

      $oldPathFile .= '/';
      $newPathFile .= '/';

      $isCopy = $this->explorer->copyFolder($oldPathFile, $newPathFile);

      if (!$isCopy['isCopy']) {
        return $data;
      }

      $data['sizeFile'] = $isCopy['sizeFile'];
      $data['oldPathFile'] = $oldPathUserFile.'/';
      $data['newPathFile'] = $newPathUserFile.'/';
    } else if (is_file($oldPathFile)) {

      if (is_file($newPathFile)) {
        for ($i = 1; $i < 9999; $i++) {
          $newPathFile = "{$this->getPathStorage()}{$newPath}{$file[0]} Копия({$i}){$file[1]}";
          if (!is_file($newPathFile)) {
            break;
          } else if ($i >= 9998) {
            return $data;
          }
        }
      }

      $isCopy = $this->explorer->copyFile($oldPathFile, $newPathFile);

      if (!$isCopy['isCopy']) {
        return $data;
      }

      $data['sizeFile'] = $isCopy['sizeFile'];
    }

    $statFile = $this->getStatFile($newPathFile, true);

    array_push($data['file'], $statFile);


    $data['status'] = true;
    return $data;
  }

  public function isSelfCopy($oldPath, $newPath)
  {
    // echo $oldPath, "\n", $newPath, "\n";
    $selfCopy = str_replace($oldPath, '', $newPath);
    // echo $selfCopy;
    return $selfCopy !== $newPath && $oldPath !== $newPath ? true : false;
  }

  public function formattedSize(int $bytes)
  {
    $formattedSize = $this->explorer->formatSizeFile($bytes);

    return $formattedSize;
  }

  public function getSizeFile($path){
    
    $path = "{$this->getPathStorage()}{$path}";
    
    if(is_file($path)){
      return filesize($path);
    }
    else{
      return $this->explorer->getSizeFolder($path);
    }

  }
}
