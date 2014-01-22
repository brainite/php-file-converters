<?php
namespace Witti\FileConverter;

if (!function_exists('drush_print')) {
  function drush_print($msg, $indent = 0) {
    echo str_repeat(' ', $indent) . $msg . "\n";
  }
}

class FileConverterTests {
  static public function factory($root = NULL) {
    return new FileConverterTests($root);
  }

  protected $root = FALSE;
  public function __construct($root = NULL) {
    if (isset($root) && is_dir($root)) {
      $this->root = realpath($root);
    }
  }

  /**
   * Run all tests configured in the folder indicated at $this->root.
   */
  public function doAllTests() {
    if (!$this->root) {
      return;
    }

    // Localize the root variable.
    $root = $this->root;

    // Configure the thumbnail sizes
    $thumb_sizes = array(
      '200x200' => TRUE,
      '50x50' => TRUE,
    );

    // Build the os suffix.
    $fc = \Witti\FileConverter\FileConverter::factory();
    $tmp = $fc->getSettings();
    $os_suffix = '-' . $tmp['operating_system'] . '_'
      . $tmp['operating_system_version'];
    drush_print("OS: " . substr($os_suffix, 1));
    //     $results_base = strtoupper('RESULTS' . $os_suffix);
    $os_suffix .= '-VERSIONPLACEHOLDER' . '.';

    // Load the version MD5s.
    $md5s_path = realpath($root . '/version_md5.json');
    if (!$md5s_path) {
      drush_print("Invalid path to tests. 'version_md5.json' was not found.");
      return;
    }
    $md5s = json_decode(file_get_contents($md5s_path), TRUE);

    // Locate and iterate over the tests.
    $tests = new \RegexIterator(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root)), '@/_test.json$@s');
    foreach ($tests as $test) {
      $conf = json_decode(file_get_contents($test), TRUE);
      if (!is_array($conf)) {
        continue;
      }
      drush_print("TEST: " . basename(dirname($test)));
      drush_print($conf['title'], 6);

      foreach ($conf['tests'] as $subtest => $subtest_conf) {
        drush_print("TITLE: " . $subtest_conf['title'], 2);

        // Create the FileConverter object.
        $fc = \Witti\FileConverter\FileConverter::factory(FALSE);

        if (isset($subtest_conf['replace-string'])) {
          $fc->setReplacements($subtest_conf['replace-string'], 'string');
        }

        // Display the test configuration.
        $tmp = dirname($test) . DIRECTORY_SEPARATOR . $subtest
          . DIRECTORY_SEPARATOR . $subtest;
        $s_paths = glob($tmp . '.*');
        if (empty($s_paths)) {
          drush_print("NO TEST FILE FOUND: $subtest", 4);
          continue;
        }
        $s_path = array_shift($s_paths);
        foreach ($conf['convert_paths'] as $test_id => $test_conf) {
          drush_print("CONVERSION: " . $test_id, 4);

          // Do the basic conversion.
          $d_path = str_replace('%', $s_path . "-$test_id", $test_conf['destination']);
          $d_path .= $os_suffix . pathinfo($d_path, PATHINFO_EXTENSION);

          // drush_print("SRC:  " . $s_path, 4);
          // drush_print("DEST: " . $d_path, 4);
          if (is_array($test_conf['engines'])) {
            foreach ($test_conf['engines'] as $engine_path => $engine_conf) {
              $fc->setConverter($engine_path, $engine_conf);
            }
            $time = microtime(TRUE);
            try {
              $fc->convertFile($s_path, $d_path);
            } catch (\Exception $e) {
              continue;
            }
            $time = microtime(TRUE) - $time;
            drush_print("TIME: " . round($time, 3) . ' seconds', 6);
          }
          if (!is_file($d_path)) {
            continue;
          }

          // Replace the version placeholder.
          $info = $fc->getVersionInfo();
          if (empty($info)) {
            $hash = 'UNKNOWNVERSION';
          }
          else {
            $hash = md5(json_encode($info));
            if (!isset($md5s[$hash])) {
              $md5s[$hash] = $info;
            }
          }
          $d_path_final = str_replace('VERSIONPLACEHOLDER', $hash, $d_path);
          rename($d_path, $d_path_final);

          // Create derivatives.
          $s_der = $d_path_final;
          $s_thumb = NULL;
          $fc_der = \Witti\FileConverter\FileConverter::factory(FALSE);
          foreach ($conf['derivatives'] as $der_id => $der_conf) {
            drush_print("DERIVATIVE: " . $der_id, 6);
            $d_der = str_replace('%', $s_der . "-$der_id", $der_conf['destination']);
            foreach ($der_conf['engines'] as $engine_path => $engine_conf) {
              $fc_der->setConverter($engine_path, $engine_conf);
            }
            //           var_dump($s_der, $d_der);
            $fc_der->convertFile($s_der, $d_der);
            if ($der_id === 'jpg' && is_file($d_der)) {
              $s_thumb = $d_der;
            }
          }

          // Create thumbnails.
          if (isset($s_thumb)) {
            $fc_thumb = \Witti\FileConverter\FileConverter::factory(FALSE);
            foreach ($thumb_sizes as $thumb_size => $thumb_create) {
              drush_print("THUMB: " . $thumb_size, 6);
              $d_thumb = $s_thumb . "-$thumb_size.jpg";
              if ($thumb_create) {
                $fc_thumb->setConverter('jpg->jpg', array(
                  '#engine' => 'Convert\\ImageMagick',
                  'resize' => $thumb_size,
                ));
                try {
                  $fc_thumb->convertFile($s_thumb, $d_thumb);
                  $fc_thumb->optimizeFile($d_thumb);
                } catch (\Exception $e) {
                  drush_print("Thumbnail error: " . $e->getMessage(), 6);
                }
              }
              elseif (is_file($d_thumb)) {
                unlink($d_thumb);
              }
            }
          }
        }
      }
    }

    // Rebuild the version md5 file.
    $lines = array();
    foreach ($md5s as $md5 => $info) {
      $lines[] = json_encode($md5) . ":" . json_encode($info);
    }
    $dat = "{\n" . implode(",\n", $lines) . "\n}";
    file_put_contents($md5s_path, $dat);
  }
}