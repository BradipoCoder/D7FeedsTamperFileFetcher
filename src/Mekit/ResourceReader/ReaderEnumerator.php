<?php
/**
 * Created by Adam Jakab.
 * Date: 28/08/15
 * Time: 13.55
 */

namespace Mekit\ResourceReader;


class ReaderEnumerator {
  /** @var  array */
  protected $readers;

  public function __construct() {
    $this->enumerateReaders();
  }

  /**
   * @param array $files
   * @param array $settings
   * @return array
   */
  public function elaborateFiles($files, $settings) {
    $answer = [];
    if(count($files)) {
      foreach($files as $fileInfo) {
        $reader = $this->getReaderFor($fileInfo);
        $answer[] = $reader->read($fileInfo, $settings);
      }
    }
    return $answer;
  }

  /**
   * @param array $form
   * @param \FeedsImporter $importer
   * @param $element_key
   * @param $settings
   * @return array
   */
  public function configForm($form, $importer, $element_key, $settings) {
    foreach($this->readers as $readerInfo) {
      /** @var  ResourceReader $reader */
      $reader = $readerInfo["instance"];
      $form = $reader->configForm($form, $importer, $element_key, $settings);
    }
    return $form;
  }

  /**
   * @param array $fileInfo
   * @return bool|ResourceReader
   * @throws \Exception
   */
  private function getReaderFor($fileInfo) {
    $answer = false;
    foreach($this->readers as $readerInfo) {
      /** @var  ResourceReader $reader */
      $reader = $readerInfo["instance"];
      if($reader->handlesExtension($fileInfo["extension"])) {
        $answer = $reader;
        break;
      }
    }
    if(!$answer) {
      throw new \Exception("Unable to find reader for extension(".$fileInfo["extension"].")!");
    }
    return $answer;
  }

  /**
   * @param string $className
   * @throws \Exception
   */
  protected function registerReader($className) {
    if (!in_array('Mekit\\ResourceReader\\ResourceReader', class_implements($className))) {
      throw new \Exception("Reader $className must implement the Mekit\\ResourceReader\\ResourceReader interface!" . json_encode(class_implements($className)));
    }
    /** @var ResourceReader $reader */
    $reader = new $className;
    $this->readers[$reader->getWeight()] = [
      'class_name' => $className,
      'extensions' => $reader->getHandledExtensions(),
      'weight' => $reader->getWeight(),
      'instance' => $reader,
    ];
  }

  protected function enumerateReaders() {
    $readerClasses = [];

    // Look for default readers
    $defaultReadersPath = dirname(__FILE__) . '/Reader';
    if ($handle = opendir($defaultReadersPath)) {
      while (false !== ($entry = readdir($handle))) {
        if(preg_match('/^.*Reader\.php$/', $entry)) {
          $entry = 'Mekit\\ResourceReader\\Reader\\' . str_replace('.php', '' , $entry);
          $readerClasses[] = $entry;
        }
      }
      closedir($handle);
    }

    // Look for other already loaded readers
    foreach (get_declared_classes() as $className) {
      if (in_array('Mekit\ResourceReader\ResourceReader', class_implements($className))) {
        $readerClasses[] = $className;
      }
    }

    // Register readers
    foreach($readerClasses as $readerClass) {
      $this->registerReader($readerClass);
    }

    // Sort and reverse order so we can loop readers from the highest to the lowest
    ksort($this->readers, SORT_NUMERIC);
    $this->readers = array_reverse($this->readers);
  }
}