<?php
/*
 * This file is part of the FileConverter package.
 *
 * (c) Greg Payne
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @file
 * Integrate with Ted.
 *
 * @link http://www.nllgg.nl/Ted/
 */

namespace Witti\FileConverter\Engine\Convert;
use Witti\FileConverter\Engine\EngineBase;
class Ted extends EngineBase {
  public function getConvertFileShell($source, &$destination) {
    return array(
      $this->cmd,
      '--printToFile',
      $source,
      $destination,
    );
  }

  protected function getHelpInstallation($os, $os_version) {
    $help = array(
      'title' => 'Ted',
    );
    switch ($os) {
      case 'Ubuntu':
        $help['os'] = 'confirmed on Ubuntu 12.04';
        $help['notes'] = array(
          'Download .deb file from http://www.nllgg.nl/Ted/#How_to_install_Ted',
          'sudo dpkg -i <package-details>.deb',
          'Ex: sudo dpkg -i ted-2.23-amd64.deb',
        );
        return $help;
    }

    return parent::getHelpInstallation($os, $os_version);
  }

  public function getVersionInfo() {
    $info = array(
      'ted' => 'unknown',
    );
    $v = $this->shell($this->cmd . " --version");
    if (preg_match('@Ted ([\d\.]+)@', $v, $arr)) {
      $info['ted'] = $arr[1];
    }
    return $info;
  }

  public function isAvailable() {
    $this->cmd = $this->shellWhich('Ted');
    return isset($this->cmd);
  }

}