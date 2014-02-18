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
    $help = array(
      'title' => 'GhostScript',
      'url' => 'http://www.ghostscript.com/',
    );
    switch ($os) {
      case 'Ubuntu':
        $help['os'] = 'confirmed on Ubuntu 12.04';
        $help['apt-get'] = 'ghostscript';
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