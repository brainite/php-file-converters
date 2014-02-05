<?php
namespace Witti\FileConverter\Engine\Convert;

use Witti\FileConverter\Engine\EngineBase;
class GhostScript extends EngineBase {
  public function getConvertFileShell($source, &$destination) {
    return array(
      'ps2pdf',
      $source,
      $destination,
    );
  }

  public function getHelpInstallation($os, $os_version) {
    $help = "This engine is managed at http://www.ghostscript.com/\n";
    switch ($os) {
      case 'Ubuntu':
        $help .= "/usr/bin/libreoffice is symlink to /usr/bin/gs\n";
        $help .= "sudo apt-get install ghostscript\n";
        return $help;
    }

    return parent::getHelpInstallation();
   }

  public function getVersionInfo() {
    $gsv = $this->shell('gs -v');
    if (preg_match('@Ghostscript ([0-9\.]+)@s', $gsv, $arr)) {
      return array(
        'gs' => $arr[1],
      );
    }
    else {
      return array(
        'gs' => 'UNKNOWN',
      );
    }
  }


  public function isAvailable() {
    $this->cmd = $this->shellWhich('gs');
    return isset($this->cmd);
  }
}