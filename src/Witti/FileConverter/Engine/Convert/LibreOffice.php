<?php
namespace Witti\FileConverter\Engine\Convert;

use Witti\FileConverter\Engine\EngineBase;
use Witti\FileConverter\Util\Shell;
class LibreOffice extends EngineBase {
  protected $cmd_source_safe = FALSE;

  public function getConvertFileShell($source, &$destination) {
    $destination = str_replace('.' . $this->conversion[0], '.'
      . $this->conversion[1], $source);
    return array(
      Shell::arg('export HOME=' . escapeshellarg($this->settings['temp_dir']) . ';', Shell::SHELL_SAFE),
      $this->cmd,
      '--headless',
      '--convert-to',
      $this->conversion[1],
      '--outdir',
      $this->settings['temp_dir'],
      $source
    );
  }

  public function getHelpInstallation($os, $os_version) {
    $help = "";
    switch ($os) {
      case 'Ubuntu':
        $help .= "/usr/bin/libreoffice is symlink to /usr/lib/libreoffice/program/soffice\n";
        $help .= "sudo apt-get install libreoffice\n";
        return $help;
    }

    return parent::getHelpInstallation();
  }

  public function getVersionInfo() {
    $info = array(
      'LibreOffice' => $this->shell($this->cmd . " --version")
    );
    $info["LibreOffice"] = preg_replace('@LibreOffice *@si', '', $info['LibreOffice']);
    return $info;
  }

  public function isAvailable() {
    $this->cmd = $this->shellWhich('libreoffice');
    return isset($this->cmd);
  }

}