<?php
namespace Witti\FileConverter;

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

    // Create the FileConverter object.
    $fc = \Witti\FileConverter\FileConverter::factory();

    // Build the os suffix.
    $tmp = $fc->getSettings();
    $os_suffix = '-' . $tmp['operating_system'] . '_' . $tmp['operating_system_version'];
    $os_suffix .= '-VERSIONPLACEHOLDER' . '.';

    // Load the version MD5s.
    $md5s_path = realpath($root . '/version_md5.json');
    $md5s = json_decode(file_get_contents($md5s_path), TRUE);

    // Locate and iterate over the tests.
    $tests = new \RegexIterator(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root)), '@/_test.json$@s');
    foreach ($tests as $test) {
      drush_print("TEST: " . basename(dirname($test)));
      $conf = json_decode(file_get_contents($test), TRUE);
      if (!is_array($conf)) {
        continue;
      }

      // Configure any replacements
      if (is_array($conf['convert_path_overrides'])) {
        foreach ($conf['convert_path_overrides'] as $k => $v) {
          switch ($k) {
            case 'replace-string':
              $fc->setReplacements($v, 'string');
              break;
          }
        }
      }

      // Display the test configuration.
      drush_print("TITLE: " . $conf['title'], 2);
      $tmp = dirname($test) . DIRECTORY_SEPARATOR . basename(dirname($test));
      $s_paths = glob($tmp . '.*');
      $s_path = array_shift($s_paths);
      foreach ($conf['convert_paths'] as $test_id => $test_conf) {
        drush_print("CONVERSION: " . $test_id, 2);

        // Do the basic conversion.
        $d_path = str_replace('%', $s_path . "-$test_id", $test_conf['destination']);
        $d_path .= $os_suffix . pathinfo($d_path, PATHINFO_EXTENSION);
        if (is_array($test_conf['engines'])) {
          foreach ($test_conf['engines'] as $engine_path => $engine_conf) {
            $fc->setConverter($engine_path, $engine_conf);
          }
          $fc->convertFile($s_path, $d_path);
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
          $d_der = str_replace('%', $s_der . "-$der_id", $der_conf['destination']);
          foreach ($der_conf['engines'] as $engine_path => $engine_conf) {
            $fc_der->setConverter($engine_path, $engine_conf);
          }
          $fc_der->convertFile($s_der, $d_der);
          if ($der_id === 'jpg' && is_file($d_der)) {
            $s_thumb = $d_der;
          }
        }

        // Create thumbnails.
        if (isset($s_thumb)) {
          $fc_thumb = \Witti\FileConverter\FileConverter::factory(FALSE);
          foreach ($thumb_sizes as $thumb_size => $thumb_create) {
            $d_thumb = $s_thumb . "-$thumb_size.jpg";
            if ($thumb_create) {
              $fc_thumb->setConverter('jpg->jpg', array(
                '#engine' => 'Convert\\ImageMagick',
                'resize' => $thumb_size,
              ));
              $fc_thumb->convertFile($s_thumb, $d_thumb);
              $fc_thumb->optimize($d_thumb);
            }
            elseif (is_file($d_thumb)) {
              unlink($d_thumb);
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