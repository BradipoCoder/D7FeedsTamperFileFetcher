<?php
/**
 * Created by Adam Jakab.
 * Date: 28/08/15
 * Time: 10.45
 */

namespace Mekit\ResourceReader\Reader;

use Mekit\ResourceReader\Reader;
use Mekit\ResourceReader\ResourceReader;

class FallbackReader extends Reader implements ResourceReader
{
  /** @var int  */
  protected $weight = -999;

  /** @var array  */
  protected $extensions = ['*'];

  /**
   * @param array $fileInfo
   * @param array $settings
   * @return string
   */
  public function read($fileInfo, $settings) {
    $answer = '';
    $settingsKey = $this->getSettingsKey(__CLASS__);
    if($settings[$settingsKey]['return'] == 'path') {
      $answer = $fileInfo['dirname'] . '/' . $fileInfo['basename'];
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
        'Fallback Reader Settings(Extensions="%extensions")',
        [
          '%extensions' => implode(", ", $this->extensions),
        ]
      ),
      '#description' => t(
        'Fallback reader is the last reader that will be executed on all files if all other readers '
        . 'refuse to read it(because file extension does not match).'
      ),
    ];

    $form[$settingsKey]['return'] = [
      '#type' => 'select',
      '#options' => [
        'nothing' => 'Nothing(empty string)',
        'path' => 'Full File Path',
      ],
      '#default_value' => isset($settings[$settingsKey]['return']) ? $settings[$settingsKey]['return'] : 'path',
      '#title' => t('Return Value'),
      '#description' => t('Use "Full File Path" if you want to get the path of the file not read by any other reader. '
      .'This can be useful for File(image/attachment/etc.) fields.'
      ),
    ];

    return $form;
  }

  /**
   * This Reader will handle any extension
   * @param string $extension
   * @return bool
   */
  public function handlesExtension($extension) {
    return true;
  }
}