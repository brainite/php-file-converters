<?php
/*
 * This file is part of the FileConverter package.
 *
 * (c) Greg Payne
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FileConverter\Engine\Convert;
use FileConverter\Engine\EngineBase;
use FileConverter\Util\Shell;
class MsgConvert extends EngineBase {
  public function getConvertFileShell($source, &$destination) {
    return array(
      $this->cmd,
      '--outfile',
      $destination,
      $source,
    );
  }

  protected function getHelpInstallation($os, $os_version) {
    $help = array(
      'title' => 'OutlookMsg via msgconv',
      'url' => 'https://www.matijs.net/software/msgconv/',
    );
    switch ($os) {
      case 'Ubuntu':
        $help['os'] = 'confirmed on Ubuntu 20.04';
        $help['apt-get'] = 'libemail-outlook-message-perl';
        return $help;
    }

    return parent::getHelpInstallation($os, $os_version);
  }

  public function getVersionInfo() {
    $info = array(
      'msgconvert' => '0',
    );
    return $info;
  }

  public function isAvailable() {
    $this->cmd = $this->shellWhich('msgconvert');
    return isset($this->cmd);
  }
}