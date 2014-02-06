<?php
namespace Witti\FileConverter\Engine\Convert;

use Witti\FileConverter\Engine\EngineBase;
use Witti\FileConverter\Util\Shell;
class AbiWord extends EngineBase {
  protected $cmd_source_safe = FALSE;

  public function getConvertFileShell($source, &$destination) {
    return array(
      $this->cmd,
      Shell::arg('to', Shell::SHELL_ARG_BASIC_DBL, $destination),
      $source
    );
  }

  public function getHelpInstallation($os, $os_version) {
    $help = "AbiWord is managed at http://www.abisource.com/\n";
    switch ($os) {
      case 'Ubuntu':
        $help .= "sudo apt-get install abiword\n";
        return $help;
    }

    return parent::getHelpInstallation($os, $os_version);
  }

  public function getVersionInfo() {
    $info = array(
      'abiword' => $this->shell($this->cmd . ' --version'),
    );
    return $info;
  }

  public function isAvailable() {
    $this->cmd = $this->shellWhich('abiword');
    return isset($this->cmd);
  }

}