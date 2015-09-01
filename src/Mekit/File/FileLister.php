<?php
/**
 * Created by Adam Jakab.
 * Date: 28/08/15
 * Time: 9.38
 */

namespace Mekit\File;


class FileLister {
  /** @var  array */
  protected $files;

  /**
   * @param string $path
   * @param string $language
   * @param string $identifier
   * @param string $extension
   */
  public function generateEmptyFile($path, $language, $identifier, $extension) {
    $realPath = drupal_realpath($path);
    if(!file_exists($realPath)) {
      return;
    }

    if($language) {
      $realPath = $realPath . '/' . $language;
      if(!file_exists($realPath)) {
        mkdir($realPath);
        $realPath = drupal_realpath($realPath);
        if(!file_exists($realPath)) {
          return;
        }
      }
    }

    $fullFilePath = $realPath . '/' . strtolower($identifier) . '.' . strtolower($extension);
    if(!file_exists($fullFilePath)) {
      touch($fullFilePath);
    }
  }

  /**
   * @param string $path
   * @param string $identifier
   * @return array
   */
  public function getFiles($path, $identifier) {
    $answer = [];
    $this->updateFileListForPath($path);
    $pathUniqueId = $this->getPathUniqueId($path);
    if(isset($this->files[$pathUniqueId]) && count($this->files[$pathUniqueId])) {
      $pattern = '#^' . $identifier . '([_-]+[0-9]+)*$#i';
      foreach($this->files[$pathUniqueId] as $fileInfo) {
        if(preg_match($pattern, $fileInfo['filename'])) {
          $answer[] = $fileInfo;
        }
      }
    }
    return $answer;
  }

  /**
   * List oll files in a directory - No subdirs(reserved to language specific content)!
   * @param string $path
   * @throws \Exception
   */
  public function updateFileListForPath($path) {
    $realPath = drupal_realpath($path);
    if(!file_exists($realPath)) {
      throw new \Exception("The specified path(".$path.") does not exist!");
    }
    $pathUniqueId = $this->getPathUniqueId($realPath);
    if(!isset($this->files[$pathUniqueId])) {
      $this->files[$pathUniqueId] = $this->getFileList($realPath);
    }
  }

  /**
   * @param string $path
   * @return string
   */
  protected function getPathUniqueId($path) {
    return md5(drupal_realpath($path));
  }

  /**
   * @param string $path
   * @return array
   * @throws \Exception
   */
  protected function getFileList($path) {
    $realPath = drupal_realpath($path);
    if(!file_exists($realPath) || !is_dir($realPath)) {
      throw new \Exception("The specified path(".$realPath.") does not exist or is not a directory!");
    }
    $files = [];
    if ($handle = opendir($realPath)) {
      while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
          $fullPath = realpath($realPath . '/' . $entry);
          if ($fullPath && is_file($fullPath)) {
            $pi = pathinfo($fullPath);
            if($pi["filename"] && $pi["extension"]) {
              $files[$pi["filename"]] = $pi;
            }
          }
        }
      }
      closedir($handle);
    }
    ksort($files);
    return $files;
  }
}