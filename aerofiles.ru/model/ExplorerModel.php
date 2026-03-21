<?php

class ExplorerModel
{

  private string $pathUser;
  private string $pathStorage;
  private array $typesFile = [
    'txt' => 'Текстовый документ',
    'html' => 'HTML файл'
  ];
  private array $typeSize = [
    'байт',
    'КБ',
    'МБ',
    'ГБ',
    'TБ',
    'ПБ',
    'ЭБ',
    'ЗБ',
    'ЙБ'
  ];
  private array $forbiddenChars = ['/', '\\', '?', '*', ':', '"', '<', '>', '|'];

  public function __construct(string $pathUser, string $pathStorage)
  {
    $this->pathUser = $pathUser;
    $this->pathStorage = $pathStorage;
  }

  public function createFolder(string $nameFolder, bool $isFullPath=false)
  {
    $isCreated = null;

    if($isFullPath){
      @$isCreated = mkdir($nameFolder);
    }
    else{
      @$isCreated = mkdir("{$this->pathStorage}{$this->pathUser}{$nameFolder}");
    }
    
    return $isCreated;
  }

  public function openFolder(string $path)
  {
    $this->pathUser = str_replace(['../', '/../', '//'], '/', $path);
  }

  public function getFilesFromCurrentPath()
  {

    $path = "{$this->pathStorage}{$this->pathUser}";
    $files = new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS);

    return [

      'path' => $path,
      'files' => $files

    ];
  }

  public function getPathUser()
  {
    return $this->pathUser;
  }
  public function getPathStorage()
  {
    return $this->pathStorage;
  }

  public function getStatFile($nameFile, $isFullPath = false)
  {
    $pathFile = null;

    if (!$isFullPath) {
      $pathFile = "{$this->pathStorage}{$this->pathUser}{$nameFile}";
    } else {
      $pathFile = $nameFile;
      $nameFile = basename($nameFile);
    }

    $statFile = stat($pathFile);

    if (!is_file($pathFile)) {
      return [
        'dataType' => ['type' => 'Folder', 'title' => 'Папка'],
        'name' => $nameFile,
        'isFile' => false,
        'size' => "",
        'ctime' => $statFile['ctime']
      ];
    }

    $typeFile = $this->getTypeFile($nameFile);
    $titleFile = $this->getTitleFile($typeFile);
    $shortFileName = $this->getShortNameFile($nameFile);
    $typeFileForImg = $this->getTypeFileForImg($typeFile);
    $formatedSize = $this->formatSizeFile($statFile['size']);

    return [
      'dataType' => ['type' => $typeFile, 'title' => $titleFile, 'img' => strtoupper($typeFileForImg)],
      'name' => $shortFileName,
      'isFile' => true,
      'fullsize' => $statFile['size'],
      'formatedSize' => $formatedSize,
      'ctime' => $statFile['ctime']
    ];
  }

  public function getTypeFile(string $nameFile)
  {

    $typeFile = substr($nameFile, strrpos($nameFile, '.') + 1, 10);
    return $typeFile;
  }

  public function getTypeFileForImg(string $typeFile)
  {

    if (!in_array($typeFile, array_keys($this->typesFile), true)) {
      return "WithoutFormat";
    }
    return $typeFile;
  }

  public function getTitleFile(string $typeFile)
  {

    if (!in_array($typeFile, array_keys($this->typesFile), true)) {
      return "{$typeFile} файл";
    }
    return $this->typesFile[$typeFile];
  }

  public function getShortNameFile($nameFile)
  {
    $fileName = substr($nameFile, 0, strrpos($nameFile, '.'));
    return $fileName;
  }

  public function getDataFromTimeStamp($timeStamp)
  {

    return date("d.m.Y G:i:s", $timeStamp);
  }

  public function formatSizeFile($sizeFile)
  {
    $countLoops = 0;
    $size = $sizeFile;
    $countTypeSizes = count($this->typeSize) - 1;

    while ($size >= 1024) {
      $size = $size / 1024;

      $countLoops++;

      if($countLoops === $countTypeSizes){
        break;
      }

    }

    $typeSize = $this->typeSize[$countLoops];

    $size = $this->formattedNumber($size);

    return "{$size} {$typeSize}";
  }

  public function formattedNumber(float $number){
    $number = (string) $number;
    $divPath = substr($number, strrpos($number, '.'));
    if($divPath === $number){
      return (float) $number;
    }
    $divPath = substr($divPath, 0, 3);
    $number = substr($number, 0, strrpos($number, '.'));

    $result = $number.$divPath;
    return (float) $result;
  }

  public function uploadFile($file)
  {
    $message = ['isMoved'=> false, 'nameFile'=> null];
    $tmpfile = $file['tmp_name'];
    $fileName = [substr($file['name'], 0, strrpos($file['name'],'.')), substr($file['name'], strrpos($file['name'], '.'))];

    $nameFile = "{$fileName[0]}{$fileName[1]}";
    $pathFile = "{$this->pathStorage}{$this->pathUser}{$nameFile}";

    if(is_file($pathFile)){
      for($i=0; $i<9999; $i++){
        $nameFile = "{$fileName[0]} ({$i}){$fileName[1]}";
        $pathFile = "{$this->pathStorage}{$this->pathUser}{$nameFile}";
        if(!is_file($pathFile)){
          break;
        }
        if($i >= 9998){
          return false;
        }
      }
    }

    $isMoved = move_uploaded_file($tmpfile, $pathFile);

    if (!$isMoved) {
      return $message;
    }

    $message['isMoved'] = true;
    $message['nameFile'] = $nameFile;
    return $message;
  }

  public function deleteFile($filePath, $isFullPath=false)
  {
    $path = null;
    
    if($isFullPath){
      $path = $filePath;
    }
    else{
      $path = "{$this->pathStorage}{$this->pathUser}{$filePath}";
    }

    $dataFile = [
      'isDeleted'=>false,
      'sizeFile'=>filesize($path)
    ];

    $isDeleted = unlink($path);

    if (!$isDeleted) {
      return $dataFile;
    }

    $dataFile['isDeleted'] = true;
    return $dataFile;
  }

  public function deleteFolder($folderName, $isFullPath=false)
  {
    $path = null;
    $data = [
      'isDeleted' => false,
      'sizeFile'=>0,
      'folders'=>[]
    ];

    if($isFullPath){
      $path = $folderName;
    }
    else{
      $path = "{$this->pathStorage}{$this->pathUser}{$folderName}";
    }

    $isDeleted = null;
    
    if ($this->isEmptyFolder($path)) {
      $isDeleted = rmdir($path);
      if (!$isDeleted) {
        return $data;
      }
    } else {
      $isDeleted = $this->clearFolder($path);
      if (!$isDeleted['isClear']) {
        return $data;
      }

      $data['sizeFile'] = $isDeleted['sizeFile'];
      $data['folders'] = $isDeleted['folders'];
      rmdir($path);
    }

    array_push($data['folders'], str_replace($this->pathStorage,'',$path).'/');
    $data['isDeleted'] = true;

    return $data;
  }

  public function isEmptyFolder($path)
  {

    $isEmpty = false;

    $files = scandir($path);
    $files = array_diff($files, ['.', '..']);

    if (count($files) === 0) {
      $isEmpty = true;
    }

    return $isEmpty;
  }

  public function clearFolder($path)
  {
    $data = [
      'isClear'=>false,
      'sizeFile'=>0,
      'folders'=>[]
    ];  

    $files = scandir($path);
    $files = array_diff($files, ['.', '..']);

    foreach ($files as $file) {
      $pathFile = "{$path}/{$file}";

      if (!is_file($pathFile)) {
        if (!$this->isEmptyFolder($pathFile)) {
          $isClear = $this->clearFolder($pathFile);
          $data['sizeFile']+=$isClear['sizeFile'];
          $data['folders'] = $isClear['folders'];
        }
        rmdir($pathFile);
        array_push($data['folders'], str_replace($this->pathStorage,'',$pathFile).'/');
      } else {
        $data['sizeFile']+=filesize($pathFile);
        unlink($pathFile);
      }
    }

    $data['isClear'] = true;

    return $data;
  }

  public function renameFile(string $oldName, string $newName): bool
  {

    if (!$this->isCorrectNameFile($newName)) {
      return false;
    }

    $oldPath = "{$this->pathStorage}{$this->pathUser}{$oldName}";
    $newPath = "{$this->pathStorage}{$this->pathUser}{$newName}";

    $filesDir = scandir("{$this->pathStorage}{$this->pathUser}");

    for($i=0;$i<count($filesDir);$i++){
      if($oldName === $filesDir[$i]){
        $filesDir[$i] = $newName;
      }
    }

    $existsFiles = [];

    for($i=0; $i<count($filesDir); $i++){
      if($filesDir[$i]===$newName){
        array_push($existsFiles, $filesDir[$i]);
      }
    }

    if((is_file($newPath) || is_dir($newPath)) && count($existsFiles)>1){
      return false;
    }

    @$isRenamed = rename($oldPath, $newPath);
    if (!$isRenamed) {
      return false;
    }

    return true;
  }

  public function isCorrectNameFile(string $nameFile): bool
  {
    if ($nameFile === '.' || $nameFile === '..') {
      return false;
    }

    $forbiddenChars = $this->forbiddenChars;
    foreach ($forbiddenChars as $char) {
      if (strpos($nameFile, $char) === -1) {
        return false;
      }
    }
    return true;
  }

  public function downloadFile(string $fileName)
  {
    $baseFileName = basename($fileName);
    header('Content-Type: application/octet-stream');
    header("Content-Disposition: attachemt; filename={$baseFileName}");
    readfile($fileName);
  }

  public function genToken(string $length)
  {
    return bin2hex(random_bytes($length));
  }

  public function copyFile(string $oldPath, string $newPath): array
  {
    $data = [
      'isCopy'=>false,
      'sizeFile'=>filesize($oldPath)
    ];

    $isCopy = @copy($oldPath, $newPath);

    if (!$isCopy) {
      return $data;
    }
    $data['isCopy'] = true;
    return $data;
  }

  public function copyFolder(string $oldPath, string $newPath)
  {
    
    $data = [
      'isCopy'=>false,
      'sizeFile'=>0
    ];

    $files = array_diff(scandir($oldPath), ['.', '..']);

    $this->createFolder($newPath, true);

    // echo $oldPath, "\n", $newPath, "\n";

    foreach ($files as $file) {

      $oldPathFolder = "{$oldPath}{$file}";
      $newPathFolder = "{$newPath}{$file}";

      // echo $oldPathFolder, "\n", $newPathFolder, "\n";

      if (is_dir($oldPathFolder)) {
        $isCreated = $this->createFolder($newPathFolder, true);

        if(!$isCreated){
          return $data;
        }

        $oldPathFolder .= '/';
        $newPathFolder .= '/';
        
        $isCopy = $this->copyFolder($oldPathFolder, $newPathFolder);

        if(!$isCopy['isCopy']){
          return $data;
        }

        $data['sizeFile'] += $isCopy['sizeFile'];

      } else {
        $isCopy = $this->copyFile($oldPathFolder, $newPathFolder);

        if(!$isCopy['isCopy']){
          return $data;
        }

        $data['sizeFile'] += $isCopy['sizeFile'];
        
      }
    }

    $data['isCopy'] = true;

    return $data;
  }
  public function getSizeFolder($path){

    $sizeDir = 0;

    $files = array_diff(scandir($path),['.','..']);

    foreach($files as $file){
      
      $newPath = "{$path}/{$file}";
      if(is_dir($newPath)){
        $sizeDir += $this->getSizeFolder($newPath);
      }
      else{
        $sizeDir += filesize($newPath);
      }

    }

    return $sizeDir;

  }
}
