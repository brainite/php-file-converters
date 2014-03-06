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

  protected function getHelpInstallation($os, $os_version) {
    $help = array(
      'title' => 'Unoconv',
      'url' => 'http://dag.wiee.rs/home-made/unoconv/',
    );
    switch ($os) {
      case 'Ubuntu':
        $help['os'] = 'confirmed on Ubuntu 12.04';
        $help['apt-get'] = 'unoconv';
        return $help;
    }

    return parent::getHelpInstallation($os, $os_version);
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