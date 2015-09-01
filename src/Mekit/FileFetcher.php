<?php
/**
 * Created by Adam Jakab.
 * Date: 28/08/15
 * Time: 9.44
 */

namespace Mekit;

use Mekit\File\FileLister;
use Mekit\ResourceReader\ReaderEnumerator;
use Mekit\ResourceReader\ResourceReader;

class FileFetcher {
  /** @var  FileLister */
  protected $fileLister;

  /** @var  ReaderEnumerator */
  protected $readerEnumerator;

  /** @var  \FeedsParserResult  */
  private $pluginResult;

  /** @var  integer */
  protected $pluginItemKey;

  /** @var  string */
  protected $pluginElementKey;

  /** @var  mixed */
  protected $pluginField;

  /** @var  array */
  protected $pluginSettings;

  /** @var  \FeedsSource */
  protected $pluginSource;


  public function __construct() {
    $this->fileLister = new FileLister();
    $this->readerEnumerator = new ReaderEnumerator();
  }

  /**
   * @param \FeedsParserResult $result
   * @param integer $item_key - the index for $result->items;
   * @param string $element_key - the key for $result->items[$item_key] or $result->current_item;
   * @param mixed $field - the field value to modify
   * @param array $settings - the settings of this plugin
   * @param \FeedsSource $source
   */
  public function setup($result, $item_key, $element_key, &$field, $settings, $source) {
    $this->pluginResult = $result;
    $this->pluginItemKey = $item_key;
    $this->pluginElementKey = $element_key;
    $this->pluginField = &$field;
    $this->pluginSettings = $settings;
    $this->pluginSource = $source;
    //dpm($this->pluginSettings, "SETTINGS");
    //dpm($this->pluginResult, "RES");
    //dpm($this->pluginSource->getConfig(), "SRC-CFG");

  }

  /**
   *
   */
  public function run() {
    $currentItem = $this->pluginResult->current_item;
    $fileIdentifier = $this->pluginSettings["identifier"];

    // Try to gather files from the language specific path
    $files = [];
    if(isset($this->pluginSettings["source_language_key"]) && $this->pluginSettings["source_language_key"]) {
      $sourceLanguageKey = $this->pluginSettings["source_language_key"];
      if(isset($currentItem[$sourceLanguageKey]) && $currentItem[$sourceLanguageKey]) {
        $filePath = $this->pluginSettings["path"] . '/' . $currentItem[$sourceLanguageKey];
        try {
          $files = $this->fileLister->getFiles($filePath, $currentItem[$fileIdentifier]);
        } catch(\Exception $e) {
          //Blamey!
        }
      }
    }

    // If still no files try either the fallback folder or the original File Search Path
    if(!count($files)) {
      if(isset($this->pluginSettings["source_language_key"])
         && $this->pluginSettings["source_language_key"]
         && isset($this->pluginSettings["language_fallback_folder"])
         && $this->pluginSettings["language_fallback_folder"]
      )
      {
          $filePath = $this->pluginSettings["path"] . '/' . $this->pluginSettings["language_fallback_folder"];
      } else {
        $filePath = $this->pluginSettings["path"];
      }
      try {
        $files = $this->fileLister->getFiles($filePath, $currentItem[$fileIdentifier]);
      } catch(\Exception $e) {
        //Blamey!
      }
    }

    if(!count($files)) {
      //Create file resource in the language specific folder
      if (isset($this->pluginSettings["create_missing_resource_with_extension"])
          && $this->pluginSettings["create_missing_resource_with_extension"]
      ) {
        $extension = $this->pluginSettings["create_missing_resource_with_extension"];
        $sourceLanguageKey = $this->pluginSettings["source_language_key"];
        $sourceLanguage = isset($currentItem[$sourceLanguageKey])
                          && $currentItem[$sourceLanguageKey] ? $currentItem[$sourceLanguageKey] : NULL;
        $this->fileLister->generateEmptyFile($this->pluginSettings["path"], $sourceLanguage, $currentItem[$fileIdentifier], $extension);
      }
    }

    //dsm("FileFetcher RUN[FILES][$this->pluginElementKey]: " . json_encode($files));
    //dsm("FileFetcher RUN[$this->pluginElementKey]: " . $this->pluginSettings["path"]);
    $data = $this->readerEnumerator->elaborateFiles($files, $this->pluginSettings);

    //dpm($data, "DATA");
    //@todo: find solution for multiple values - for now concatenating
    $data = implode("", $data);
    //dsm("FileFetcher[$this->pluginElementKey] value: " . $data);
    if(!empty($data)) {
      $this->pluginField = $data;
    }
  }

  /**
   * @return ReaderEnumerator
   */
  public function getReaderEnumerator() {
    return $this->readerEnumerator;
  }
}