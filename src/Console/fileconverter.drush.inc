<?php
use FileConverter\drush_print;
/*
 * This file is part of the FileConverter package.
 *
 * (c) Greg Payne
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
    'bootstrap' => DRUSH_BOOTSTRAP_DRUSH,
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
      ),
      'allow-remote' => array(
        'description' => 'Allow remote paths (e.g., http://)',
      ),
      'replace-string' => array(
        'description' => 'JSON Object: Configure text replacements',
        'example-value' => '{"search":"replace"}',
      ),
    ),
    'examples' => array(
      'Simple jpg->png conversion' => 'fileconverter source.jpg destination.png',
      'HTML to PDF using Xhtml2Pdf' => 'fileconverter --engines=\'{"html->pdf":"xhtml2pdf:default"}\' source.html destination.pdf',
    ),
  );
  $items['fileconverter-list'] = array(
    'description' => 'List FileConverter details',
    'bootstrap' => DRUSH_BOOTSTRAP_DRUSH,
    'arguments' => array(
      'list_name' => 'The list to display (converters|convert-paths|extension-table)',
      'example-value' => 'converters',
    ),
    'options' => array(
      'show-paths' => array(
        'description' => 'Show all paths for each converter.',
      ),
    ),
  );
  $items['fileconverter-tests'] = array(
    'description' => 'Run all FileConverter tests',
    'bootstrap' => DRUSH_BOOTSTRAP_DRUSH,
    'arguments' => array(
      'path_to_tests' => 'The path of the test branch',
    ),
    'options' => array(
      'test' => array(
        'description' => 'Specify a certain test group or specific test',
        'example-value' => 'rdf2pdf or rdf2pdf/001',
      ),
      'converter' => array(
        'description' => 'Specify converters (csv) to test using the key from _test.json.',
        'example-value' => 'pandoc or pandoc,htmldoc',
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
  require_once dirname(dirname(__DIR__)) . '/autoload.php.dist';

  if (drush_get_context('DRUSH_VERBOSE')) {
    drush_print("File Converter Default Settings:");
    $fc = FileConverter\FileConverter::factory();
    foreach ($fc->getSettings() as $k => $v) {
      drush_print(sprintf('% -25s: %s', $k, $v), 3);
    }
  }
}

function drush_fileconverter_convert($source = NULL, $destination = NULL) {
  // Require the arguments.
  if (!isset($source) || !isset($destination) || $destination === '') {
    drush_print("USAGE: fileconverter 'source.ext1' 'destination.ext2'");
    return;
  }

  // Normalize the file paths.
  $fc = FileConverter\FileConverter::factory();
  $stdin = $stdout = FALSE;
  $force_conversion = trim(drush_get_option('conversion'));
  $is_remote = FALSE;
  if (preg_match('@^(.*):(.*)$@', $force_conversion, $arr)) {
    $force_conversion = array(
      $arr[1],
      $arr[2],
    );
  }
  else {
    $force_conversion = NULL;
  }
  if ($source === '-') {
    if (!isset($force_conversion)) {
      drush_print("Error: When using stdin, you must explicitly define conversion.");
      drush_print("Usage: fileconverter - output.dat --conversion=misc:dat");
      return;
    }
    $stdin = TRUE;
    $source = $fc->getEngine(NULL, NULL)->getTempFile($force_conversion[0]);
    $source_fp = fopen($source, 'w');
    while (!feof(STDIN)) {
      fputs($source_fp, fgets(STDIN));
    }
    fclose($source_fp);
  }
  else {
    if (!is_file($source)) {
      if (is_file(drush_cwd() . '/' . $source)) {
        $source = drush_cwd() . '/' . $source;
      }
    }
    if (!is_file($source)) {
      if (drush_get_option('allow-remote') === TRUE
        && strpos($source, '://') !== FALSE) {
        $is_remote = TRUE;
      }
      else {
        drush_print(dt("Error: Unable to locate source file: !source", array(
          'source' => $source,
        )));
        return;
      }
    }
  }
  if ($destination{0} !== '/') {
    if ($destination === '-') {
      if (!isset($force_conversion)) {
        drush_print("Error: When using stdout, you must explicitly define conversion.");
        drush_print("Usage: fileconverter input.dat - --conversion=misc:dat");
        return;
      }
      $stdout = TRUE;
      $destination = $fc->getEngine(NULL, NULL)->getTempFile($force_conversion[1]);
    }
    else {
      $destination = drush_cwd() . '/' . $destination;
    }
  }

  // Create the file converter and apply any cli options.
  $replace = drush_get_option('replace-string');
  if ($replace) {
    $dat = json_decode($replace, TRUE);
    if (is_array($dat)) {
      $fc->setReplacements($dat, 'string');
    }
  }

  // Convert the file.
  $fc = FileConverter\FileConverter::factory();
  if ($is_remote || realpath($source) !== realpath($destination)) {
    $convert_path = isset($force_conversion) ? join('->', $force_conversion)
      : NULL;
    try {
      $fc->convertFile($is_remote ? $source : realpath($source), $destination, $convert_path);
    } catch (\Exception $e) {
      drush_print($e->getMessage());
      return;
    }
  }

  // Further commands can work on a single file, so use a default destination.
  if (!isset($destination)) {
    $destination = $source;
  }

  // Optimize the file.
  if (drush_get_option('optimize') === TRUE) {
    $fc->optimizeFile($destination, $destination);
  }

  if ($stdin) {
    unlink($source);
  }
  if ($stdout) {
    readfile($destination);
    unlink($destination);
  }
}

function drush_fileconverter_list($list_name = NULL) {
  $items = array();

  switch ($list_name) {
    case 'converters':
      $fc = FileConverter\FileConverter::factory();
      foreach ($fc->getEngineConfigurations() as $configurations) {
        foreach ($configurations->getAllConverters() as $convert_path => $converters) {
          foreach ($converters as $id => $converter) {
            $item = array(
              '#title' => $id,
            );
            $item['Engine'] = preg_replace('@^.*\\\\@', '', $converter['#engine']);
            if ($converter['chain']) {
              $item['Engine'] .= ' ' . $converter['chain'];
            }

            $items[$id] = $item;
          }
        }
      }
      break;

    case 'convert-paths':
      $fc = FileConverter\FileConverter::factory();
      foreach ($fc->getEngineConfigurations() as $configurations) {
        foreach ($configurations->getAllConverters() as $convert_path => $converters) {
          $items[$convert_path]['#title'] = $convert_path;
          foreach ($converters as $id => $converter) {
            $engine = preg_replace('@^.*\\\\@', '', $converter['#engine']);
            if ($converter['chain']) {
              $engine .= ' ' . $converter['chain'];
            }
            $item = sprintf('%- 28s %s', $id, $engine);
            $items[$convert_path][] = $item;
          }
        }
      }
      break;

    case 'extension-table':
      $rows = array();
      $fc = FileConverter\FileConverter::factory();
      $all_tos = array();
      foreach ($fc->getEngineConfigurations() as $configurations) {
        foreach ($configurations->getAllConverters() as $convert_path => $converters) {
          if (preg_match('@^(.*)->(.*)$@', $convert_path, $arr)) {
            $froms = explode('|', trim($arr[1], '()'));
            $tos = explode('|', trim($arr[2], '()'));
          }
          elseif (preg_match('@^(.*)~optimize$@', $convert_path, $arr)) {
            $froms = $tos = array(
              $arr[1],
            );
          }
          else {
            continue;
          }
          foreach ($froms as $from) {
            if (!isset($rows[$from])) {
              $rows[$from] = array();
            }
            foreach ($tos as $to) {
              $all_tos[] = $to;
              if (!isset($rows[$to])) {
                $rows[$to] = array();
              }
              $rows[$from][$to] += sizeof($converters);
            }
          }
        }
      }

      // Build the table.
      $all_tos = array_unique($all_tos);
      sort($all_tos);
      ksort($rows);
      $exts = array_keys($rows);
      echo "source | " . join(" | ", $all_tos) . "\n";
      echo str_repeat("--- | ", sizeof($all_tos)) . "---\n";
      foreach ($rows as $from => $row) {
        if (empty($row)) {
          continue;
        }
        echo $from;
        foreach ($all_tos as $to) {
          if (isset($row[$to]) && $row[$to]) {
            echo " | " . $row[$to];
          }
          else {
            echo " | ";
          }
        }
        echo "\n";
      }
      break;

    default:
      drush_print("Usage: fileconverter list [converters|convert-paths|extension-table]");
      return;
  }

  // Sort and display the items.
  ksort($items);
  foreach ($items as $id => $item) {
    drush_print($item['#title']);
    foreach ($item as $k => $v) {
      if ($k{0} !== '#') {
        if (is_array($v)) {
          drush_print($v['#title'], 3);
          foreach ($v as $k1 => $v1) {
            if ($k1{0} !== '#') {
              drush_print($v1, 6);
            }
          }
        }
        else {
          drush_print($v, 3);
        }
      }
    }
  }
}

function drush_fileconverter_tests($path_to_tests = NULL) {
  if (!isset($path_to_tests)) {
    drush_print("USAGE: fileconverter tests <path_to_tests>");
    return;
  }
  $root = realpath(drush_cwd() . '/' . $path_to_tests);
  if (!$root || !$path_to_tests || !is_dir($root)) {
    drush_print("Unable to locate tests.");
    return;
  }

  try {
    $tester = \FileConverter\FileConverterTests::factory($root);
    $tester->setTestFilter(trim(drush_get_option('test')));
    $tester->setTestConverters(trim(drush_get_option('converter')));
    $tester->doAllTests();
  } catch (\Exception $e) {
    drush_print($e->getMessage());
  }
}