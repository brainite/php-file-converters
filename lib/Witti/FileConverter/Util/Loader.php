<?php
namespace Witti\FileConverter\Util;
class Loader {
  static public function loadAll() {
    // Recurse through all class files.
    // Include *Base on first pass.
    // Include all others on second pass.
    $requires = array();
    $dir = __DIR__ . strtr('/../', '/', DIRECTORY_SEPARATOR);
    $ritit = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);
    foreach ($ritit as $path) {
      if (is_file($path) && substr($path, -4) === '.php') {
        if (strpos($path, 'Base') !== FALSE) {
          require_once $path;
        }
        else {
          $requires[] = $path;
        }
      }
    }
    foreach ($requires as $path) {
      require_once $path;
    }
  }

}