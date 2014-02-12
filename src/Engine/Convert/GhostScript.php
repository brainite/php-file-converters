<?php
/*
 * This file is part of the Witti FileConverter package.
 *
 * (c) Greg Payne
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

  protected function getHelpInstallation($os, $os_version) {
    $help = "This engine is managed at http://www.ghostscript.com/\n";
    switch ($os) {
      case 'Ubuntu':
        $help .= "/usr/bin/libreoffice is symlink to /usr/bin/gs\n";
        $help .= "sudo apt-get install ghostscript\n";
        return $help;
    }

    return parent::getHelpInstallation($os, $os_version);
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