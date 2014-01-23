<?php
/**
 * @file
 *   Access the php-file-converters library via a drush command.
 */

/**
 * Implementation of hook_drush_command().
 *
 * You can SYMLINK this file to any of the following:
 *     1. A .drush folder in your HOME folder.
 *     2. Anywhere in a folder tree below an active module on your site.
 *     3. /usr/share/drush/commands (configurable)
 *     4. In an arbitrary folder specified with the --include option.
 *     5. Drupal's /drush or /sites/all/drush folders.
 */
function fileconverter_drush_command() {
  $items = array();
  $items['fileconverter-convert'] = array(
    'description' => 'Convert a file.',
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_CONFIGURATION,
    'aliases' => array(
      'convert'
    ),
    'arguments' => array(
      'source' => 'The path of the file to convert',
      'destination' => 'The destination path for the converted file',
    ),
    'options' => array(
      'engines' => array(
        'description' => 'JSON Object: Force a specific conversion engine',
        'example-value' => '{"html->pdf":"xhtml2pdf:default"}',
      ),
      'conversion' => array(
        'description' => 'Force a specific file type conversion (ignore extensions)',
        'example-value' => 'html:pdf',
      ),
      'optimize' => array(
        'description' => 'Optimize the destination file',
        'example-value' => '',
      ),
      'replace-string' => array(
        'description' => 'JSON Object: Configure text replacements',
        'example-value' => '{"search":"replace"}',
      ),
    ),
    'examples' => array(
      'Simple jpg->png conversion' => 'drush convert source.jpg destination.png',
      'HTML to PDF using Xhtml2Pdf' => 'drush convert --converter:html->pdf=xhtml2pdf:default source.html destination.pdf',
    ),
  );
  $items['fileconverter-tests'] = array(
    'description' => 'Run all FileConverter tests',
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_CONFIGURATION,
    'arguments' => array(
      'path_to_tests' => 'The path of the test branch',
    ),
    'options' => array(
      'test' => array(
        'description' => 'Specify a certain test group or specific test',
        'example-value' => 'rdf2pdf or rdf2pdf/001',
      ),
    ),
  );
  return $items;
}

/**
 * Include all of the required files for any fileconverter command.
 */
function fileconverter_drush_init() {
  // Examine the current command and return quickly for anything not in this file.
  $cmd = drush_get_command();
  if ($cmd["commandfile"] !== 'fileconverter') {
    return;
  }

  // Load ALL of the classes.
  require_once __DIR__ . '/../lib/Witti/FileConverter/Util/Loader.php';
  \Witti\FileConverter\Util\Loader::loadAll();

  if (drush_get_context('DRUSH_VERBOSE')) {
    drush_print("File Converter Default Settings:");
    $fc = Witti\FileConverter\FileConverter::factory();
    foreach ($fc->getSettings() as $k => $v) {
      drush_print(sprintf('% -25s: %s', $k, $v), 3);
    }
  }
}

function drush_fileconverter_convert(string $source = NULL, string $destination = NULL) {
  // Normalize the file paths.
  if (!is_file($source)) {
    if (is_file(drush_cwd() . '/' . $source)) {
      $source = drush_cwd() . '/' . $source;
    }
  }
  if (!is_file($source)) {
    drush_print(dt("Error: Unable to locate source file."));
    return;
  }
  if (isset($destination) && $destination{0} !== '/') {
    $destination = drush_cwd() . '/' . $destination;
  }

  // Create the file converter and apply any cli options.
  $fc = Witti\FileConverter\FileConverter::factory();
  $replace = drush_get_option('replace-string');
  if ($replace) {
    $dat = json_decode($replace, TRUE);
    if (is_array($dat)) {
      $fc->setReplacements($dat, 'string');
    }
  }
  if (isset($destination) && realpath($source) !== realpath($destination)) {
    $fc->convertFile(realpath($source), $destination);
  }

  // Further commands can work on a single file, so use a default destination.
  if (!isset($destination)) {
    $destination = $source;
  }

  // Optimize the file.
  if (drush_get_option('optimize') === TRUE) {
    $fc->optimizeFile($destination, $destination);
  }
}

function drush_fileconverter_tests($path_to_tests = NULL) {
  if (!isset($path_to_tests)) {
    drush_print("USAGE: drush fileconverter-tests <path_to_tests>");
    return;
  }
  $root = realpath(drush_cwd() . '/' . $path_to_tests);
  if (!$root || !$path_to_tests || !is_dir($root)) {
    drush_print("Unable to locate tests.");
    return;
  }

  try {
    $tester = \Witti\FileConverter\FileConverterTests::factory($root);
    $tester->setTestFilter(trim(drush_get_option('test')));
    $tester->doAllTests();
  } catch (\Exception $e) {
    drush_print($e->getMessage());
  }
}