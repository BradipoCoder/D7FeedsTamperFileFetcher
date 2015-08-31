<?php
/**
 * Created by Adam Jakab.
 * Date: 28/08/15
 * Time: 13.53
 */

namespace Mekit\ResourceReader;

class Reader {
  /** @var  int */
  protected $weight = 0;

  /** @var array  */
  protected $extensions = [];

  /**
   * @param array $form
   * @param \FeedsImporter $importer
   * @param $element_key
   * @param $settings
   * @return array
   */
  public function configForm($form, $importer, $element_key, $settings) {
    return $form;
  }

  /**
   * @param string $readerClass
   * @return string
   */
  protected function getSettingsKey($readerClass) {
    return md5($readerClass);
  }

  /**
   * @param string $extension
   * @return bool
   */
  public function handlesExtension($extension) {
    return in_array($extension, $this->extensions);
  }

  /**
   * @return array
   */
  public function getHandledExtensions() {
    return $this->extensions;
  }

  /**
   * @return int
   */
  public function getWeight() {
    return $this->weight;
  }
}