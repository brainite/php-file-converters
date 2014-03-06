<?php
/*
 * This file is part of the FileConverter package.
 *
 * (c) Greg Payne
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

  protected function getHelpInstallation($os, $os_version) {
    $help = array(
      'title' => 'AbiWord',
      'url' => 'http://www.abisource.com/',
    );
    switch ($os) {
      case 'Ubuntu':
        $help['os'] = 'confirmed on Ubuntu 12.04';
        $help['apt-get'] = 'abiword';
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