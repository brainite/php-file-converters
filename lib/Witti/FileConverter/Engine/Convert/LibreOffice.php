<?php
namespace Witti\FileConverter\Engine\Convert;

use Witti\FileConverter\Engine\EngineBase;
class LibreOffice extends EngineBase {
  public function getConvertFileShell($source, &$destination) {
    $destination = str_replace('.' . $this->conversion[0], '.'
      . $this->conversion[1], $source);
    return array(
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
    switch ($os) {
      case 'Ubuntu':
        return "sudo apt-get install libreoffice";
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
    $this->cmd = $this->shellWhich('soffice');
    return isset($this->cmd);
  }

}