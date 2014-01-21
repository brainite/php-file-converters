<?php
namespace Witti\FileConverter\Engine\Convert;

use Witti\FileConverter\Engine\EngineBase;
use Witti\FileConverter\Util\Shell;
class Unoconv extends EngineBase {
  protected $cmd_source_safe = FALSE;

  public function getConvertFileShell($source, &$destination) {
    $destination = str_replace('.' . $this->conversion[0], '.'
      . $this->conversion[1], $source);
    return array(
      $this->cmd,
      Shell::arg('format', Shell::SHELL_ARG_BASIC_DBL_NOEQUAL, $this->conversion[1]),
      Shell::arg('outputpath', Shell::SHELL_ARG_BASIC_DBL_NOEQUAL, $this->settings['temp_dir']),
      $source
    );
  }

  public function getHelpInstallation($os, $os_version) {
    $help = "Unoconv is managed at http://dag.wiee.rs/home-made/unoconv/\n";
    switch ($os) {
      case 'Ubuntu':
        $help .= "sudo apt-get install unoconv\n";
        return $help;
    }

    return parent::getHelpInstallation();
  }

  public function getVersionInfo() {
    $info = array(
      'unoconv' => 'unknown',
      'python' => 'unknown',
    );
    $v = $this->shell($this->cmd . " --version");
    if (preg_match('@unoconv ([\d\.]+)@', $v, $arr)) {
      $info['unoconv'] = $arr[1];
    }
    if (preg_match('@python ([\d\.]+)@', $v, $arr)) {
      $info['python'] = $arr[1];
    }
    return $info;
  }

  public function isAvailable() {
    $this->cmd = $this->shellWhich('unoconv');
    return isset($this->cmd);
  }

}