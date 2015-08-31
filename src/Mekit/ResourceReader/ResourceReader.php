<?php
/**
 * Created by Adam Jakab.
 * Date: 28/08/15
 * Time: 10.41
 */

namespace Mekit\ResourceReader;


interface ResourceReader {

  /**
   * @param array $fileInfo
   * @param array $settings
   * @return mixed
   */
  function read($fileInfo, $settings);

  /**
   * @param array $form
   * @param \FeedsImporter $importer
   * @param $element_key
   * @param $settings
   * @return array
   */
  function configForm($form, $importer, $element_key, $settings);

  /**
   * @param string $extension
   * @return bool
   */
  function handlesExtension($extension);

  /**
   * @return array
   */
  function getHandledExtensions();

  /**
   * @return int
   */
  function getWeight();
}