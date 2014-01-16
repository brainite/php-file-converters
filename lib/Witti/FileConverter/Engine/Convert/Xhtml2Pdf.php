<?php
namespace Witti\FileConverter\Engine\Convert;

use Witti\FileConverter\Engine\EngineBase;
use Witti\FileConverter\Util\Shell;
class Xhtml2Pdf extends EngineBase {
  // Set default properties
//   $this->set('html', 1)
//   ->set('base', 'http://' . $_SERVER['HTTP_HOST'] . '/');
//     // Build the make options.
//     $validOpts = Array(
//         'base',
//         'css',
//         'encoding',
//         'xml',
//         'html',
//     );
//     $optStr = '';
//     foreach ($this->getByArray($validOpts) as $k => $v) {
//       if ($v) {
//         $optStr .= " --$k ";
//         if ($v != 1) {
//           $optStr .= $v . ' ';
//         }
//       }
//     }

//     // Convert the HTML using pisa
//     $cmd = "cat '$old' | xhtml2pdf -q $optStr '-' > '$new'";
//     $errMsg = exec($cmd);

//     // Pisa seems to create an empty -.pdf file when '-' is specified for stdout.
//     if (is_file('-.pdf')) {
//       @unlink('-.pdf');
//     }

  public function convertFile($source, $destination) {
    if (!isset($this->cmd)) {
      return FALSE;
    }

    $conf = array_merge(array(
      'quiet' => FALSE,
    ), $this->configuration);

    // Work with temporary files.
    $s_path = $this->getTempFile($this->conversion[0]);
    $d_path = str_replace('.' . $this->conversion[0], '.'
      . $this->conversion[1], $s_path);
    copy($source, $s_path);

    // Convert the temporary file to the destination extension.
    $shell = array();
    $shell[] = $this->cmd;
    $shell[] = Shell::argDouble('quiet', $conf['quiet']);
    $shell[] = $s_path;
    $shell[] = $d_path;
    $output = $this->shell($shell);

    // Remove the original temporary file.
    unlink($s_path);

    // Move the converted temporary file to the destination.
    if (!is_file($d_path)) {
      echo $output . "\n";
      return FALSE;
    }
    rename($d_path, $destination);

    //     xhtml2pdf [--base=base path] [--css=CSS file] [--css-dump] [--debug]
    //     [--encoding=character encoding] [--quiet] [--warn] [--xml]
    //     [--xhtml] [--html] [SRC] [DEST]
  }

  public function getHelpInstallation($os, $os_version) {
    return "This engine is a placeholder and is not yet ready for use.";

    $output = "Xhtml2Pdf can be found online at http://www.xhtml2pdf.com/\n";
    switch ($os) {
      case 'Ubuntu':
        $output .= " Ubuntu (12.04)\n";
        $output .= "  sudo apt-get install python-pisa\n";
        return $output;
    }

    return parent::getHelpInstallation();
  }

  public function isAvailable() {
    return FALSE;
  }
}