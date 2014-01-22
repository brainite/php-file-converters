<?php
// Wrap the command in a function to avoid global namespace clutter.
function witti_fileconverter_cli() {
  // Load ALL of the FileConverter classes.
  require_once __DIR__ . '/../lib/Witti/FileConverter/Util/Loader.php';
  \Witti\FileConverter\Util\Loader::loadAll();

  // Parse the CLI arguments.
  $args = array(
    'replace-string' => NULL,
    'optimize' => NULL,
    0 => NULL,
    1 => NULL,
    2 => NULL,
  );
  $index = 0;
  foreach ($_SERVER['argv'] as $argi => $arg) {
    if ($argi == 0) {
      continue;
    }
    if (preg_match('@^--(.*?)=(.*)$@s', $arg, $arr)) {
      $args[$arr[1]] = $arr[2];
    }
    elseif (preg_match('@^--(.*)$@s', $arg, $arr)) {
      $args[$arr[1]] = TRUE;
    }
    elseif (preg_match('@^-(.*)$@s', $arg, $arr)) {
      $args[$arr[1]] = TRUE;
    }
    else {
      $args[$index++] = $arg;
    }
  }

  // Run the appropriate command.
  switch ($args[0]) {
    case 'convert':
    // Normalize the file paths.
      $source = realpath(getcwd() . '/' . $args[1]);
      if (!is_file($source)) {
        echo "Error: Unable to locate source file.\n";
        return;
      }
      $destination = !empty($args[2]) ? $args[2] : '';
      if ($destination === '') {
        $destination = $source;
      }
      elseif ($destination{0} !== '/') {
        $destination = getcwd() . '/' . $destination;
      }

      // Create the file converter and apply any cli options.
      $fc = Witti\FileConverter\FileConverter::factory();
      $replace = $args['replace-string'];
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
      if ($destination === '') {
        $destination = $source;
      }

      // Optimize the file.
      if ($args['optimize'] === TRUE) {
        $fc->optimizeFile($destination, $destination);
      }
      break;

    case 'tests':
      if (!isset($args[1])) {
        echo "USAGE: filconverter.php tests <path_to_tests>\n";
        return;
      }
      $path_to_tests = $args[1];
      $root = realpath(getcwd() . '/' . $path_to_tests);
      if (!$root || !$path_to_tests || !is_dir($root)) {
        echo "Unable to locate tests.\n";
        return;
      }

      try {
        $tester = \Witti\FileConverter\FileConverterTests::factory($root);
        $tester->doAllTests();
      } catch (\Exception $e) {
        echo $e->getMessage() . "\n";
      }
      break;

    default:
      echo "Invalid command.\n";
      return;
  }
}
witti_fileconverter_cli();