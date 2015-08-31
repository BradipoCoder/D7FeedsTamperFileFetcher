<?php
/**
 * Created by Adam Jakab.
 * Date: 28/08/15
 * Time: 10.38
 */

namespace Mekit\ResourceReader\Reader;

use Mekit\ResourceReader\Reader;
use Mekit\ResourceReader\ResourceReader;

class TextReader extends Reader implements ResourceReader
{
  /** @var int  */
  protected $weight = -1;

  /** @var array  */
  protected $extensions = ['txt', 'htm', 'html'];

  /**
   * @param array $fileInfo
   * @param array $settings
   * @return string
   */
  public function read($fileInfo, $settings) {
    $answer = '';
    $settingsKey = $this->getSettingsKey(__CLASS__);
    $fullFilePath = $fileInfo['dirname'] . '/' . $fileInfo['basename'];
    if(file_exists($fullFilePath)) {
      $content = trim(@file_get_contents($fullFilePath));
      if($content) {
        $answer = trim($content);
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
    $settingsKey = $this->getSettingsKey(__CLASS__);

    $form[$settingsKey] = [
      '#type' => 'fieldset',
      '#title' => t(
        'Text Reader Settings(Extensions="%extensions")',
        [
          '%extensions' => implode(", ", $this->extensions),
        ]
      ),
      '#description' => t(
        'Reads text files.'
      ),
    ];

    return $form;
  }
}