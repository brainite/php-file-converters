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

  protected function getHelpInstallation($os, $os_version) {
    $help = array(
      'title' => 'Unrtf',
      'url' => 'http://www.gnu.org/software/unrtf/',
    );
    switch ($os) {
      case 'Ubuntu':
        $help['os'] = 'confirmed on Ubuntu 12.04';
        $help['apt-get'] = 'unrtf';
        return $help;
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