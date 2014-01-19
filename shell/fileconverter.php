<?php
// Wrap the command in a function to avoid global namespace clutter.
function witti_fileconverter_cli() {
  // Load ALL of the FileConverter classes.
  require_once __DIR__ . '/../lib/Witti/FileConverter/Util/Loader.php';
  \Witti\FileConverter\Util\Loader::loadAll();

  // Parse the CLI arguments.
  $args = array();
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