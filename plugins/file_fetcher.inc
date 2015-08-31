<?php
require_once drupal_get_path('module', 'feeds_tamper_file_fetcher') . '/vendor/autoload.php';

$plugin = array(
  'form' => 'feeds_tamper_plugin_file_fetcher_form',
  // Optional validation callback.
  'validate' => 'feeds_tamper_plugin_file_fetcher_validate',
  'callback' => 'feeds_tamper_plugin_file_fetcher_callback',
  'name' => 'File Fetcher',
  'multi' => 'loop',
  'category' => 'Extra',
);

/**
 * @param \FeedsImporter $importer
 * @param $element_key
 * @param $settings
 * @return array
 */
function feeds_tamper_plugin_file_fetcher_form($importer, $element_key, $settings) {
  $importerConfig = $importer->getConfig();
  //dpm($importerConfig,"IMP-CFG");
  $form = [];
  $form['#tree'] = true;

  $sourceColumns = [];
  $normal = feeds_tamper_get_unique_source_list($importer, FALSE);
  $lower = feeds_tamper_get_unique_source_list($importer, TRUE);
  foreach (array_combine($normal, $lower) as $source => $mapsource) {
    if (isset($source_configs[$source]) && !empty($source_configs[$source]['name'])) {
      $sourceColumns[$mapsource] = $source_configs[$source]['name'];
    }
    else {
      $sourceColumns[$mapsource] = $source;
    }
  }

  $defaultPath = '';
  if(isset($importerConfig['processor']['config']['bundle'])) {
    $defaultPath = 'private://import/'
                   . $importerConfig['processor']['config']['bundle']
                   . '/' . $element_key;
  }
  $form['path'] = [
    '#type' => 'textfield',
    '#default_value' => isset($settings['path']) ? $settings['path'] : $defaultPath,
    '#title' => t('File Search Path'),
    '#description' => t('Indicate the path where to search for files.'),
  ];


  $defaultIdentifier = '';
  if(isset($importerConfig['processor']['config']['mappings'])) {
    foreach($importerConfig['processor']['config']['mappings'] as $mapping) {
      if(isset($mapping["unique"]) && $mapping["unique"] == true) {
        $defaultIdentifier = $mapping["source"];
        break;
      }
    }
  }

  $form['identifier'] = array(
    '#type' => 'select',
    '#default_value' => isset($settings['identifier']) ? $settings['identifier'] : $defaultIdentifier,
    '#options' => $sourceColumns,
    '#title' => t('File Identifier'),
    '#description' => t('Select which column to use from your source to identify the file(s) to use.'
    . '<br />Files must be named so to start with the string contained in this column.'),
  );


  $default_source_language_key = '';
  if(isset($importerConfig['processor']['config']['source_language_key'])) {
    $default_source_language_key = $importerConfig['processor']['config']['source_language_key'];
  }
  $form['source_language_key'] = array(
    '#type' => 'textfield',
    '#default_value' => isset($settings['source_language_key']) ? $settings['source_language_key'] : $default_source_language_key,
    '#size' => 32,
    '#title' => t('Language Column'),
    '#description' => t(
      'Specify the column in your source which contains the language key. '
      . '<br />If set files will be searched for in the subfolder of File Search Path for that language. '
      . '<br />If not set the original File Search Path will be used.'
    ),
  );

  $default_language_fallback_folder = 'en';
  $form['language_fallback_folder'] = array(
    '#type' => 'textfield',
    '#default_value' => isset($settings['language_fallback_folder']) ? $settings['language_fallback_folder'] : $default_language_fallback_folder,
    '#size' => 16,
    '#title' => t('Language Fallback Directory'),
    '#description' => t(
      'Try to look for files in this subfolder if no files were found in the language folder.'
      . '<br />If not set the fallback directory will be the original File Search Path.'
    ),
  );

  // Allow Readers to add their own stuff here
  $fileFetcher = _feeds_tamper_plugin_file_fetcher_get_class();
  $readerEnumerator = $fileFetcher->getReaderEnumerator();
  $form = $readerEnumerator->configForm($form, $importer, $element_key, $settings);

  return $form;
}

function feeds_tamper_plugin_file_fetcher_validate(&$settings) {
  if(!file_exists(drupal_realpath($settings['path']))) {
    form_set_error('settings][path', t('Invalid path or no access!'));
  }
}

/**
 * @param \FeedsParserResult $result
 * @param integer $item_key - the index for $result->items;
 * @param string $element_key - the key for $result->items[$item_key] or $result->current_item;
 * @param mixed $field - the field value to modify
 * @param array $settings - the settings of this plugin
 * @param \FeedsSource $source
 */
function feeds_tamper_plugin_file_fetcher_callback($result, $item_key, $element_key, &$field, $settings, $source) {
  $fileFetcher = _feeds_tamper_plugin_file_fetcher_get_class();
  $fileFetcher->setup($result, $item_key, $element_key, $field, $settings, $source);
  try {
    $fileFetcher->run();
  } catch(Exception $e) {
    drupal_set_message("FileFetcher Exception[$element_key]" . $e->getMessage());
  }
}


/**
 * @return \Mekit\FileFetcher
 */
function _feeds_tamper_plugin_file_fetcher_get_class() {
  if(!isset($GLOBALS['feeds_tamper_plugin_file_fetcher_class'])) {
    $GLOBALS['feeds_tamper_plugin_file_fetcher_class'] = new Mekit\FileFetcher();
  }
  return $GLOBALS['feeds_tamper_plugin_file_fetcher_class'];
}