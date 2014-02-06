<?php
namespace Witti\FileConverter\Engine\Convert;

use Witti\FileConverter\Engine\EngineBase;
use Witti\FileConverter\Util\Shell;
class Unrtf extends EngineBase {
  public function getConvertFileShell($source, &$destination) {
    $err = $this->getTempFile('err');
    return array(
      $this->cmd,
      '--ps',
      $source,
      Shell::arg('>', Shell::SHELL_SAFE),
      $destination,
      Shell::arg('STDERR', Shell::SHELL_STDERR, $err),
    );
  }

  public function getHelpInstallation($os, $os_version) {
    $output = "Unrtf is managed at http://www.gnu.org/software/unrtf/\n";
    switch ($os) {
      case 'Ubuntu':
        $output .= "sudo apt-get install unrtf\n";
        return $output;
    }

    return parent::getHelpInstallation($os, $os_version);
  }

  public function getVersionInfo() {
    $info = array(
      'unrtf' => $this->shell('unrtf --version'),
    );
    return $info;
  }

  public function isAvailable() {
    $this->cmd = $this->shellWhich('unrtf');
    return isset($this->cmd);
  }
}