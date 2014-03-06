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
class Docverter extends EngineBase {
  protected $cmd_source_safe = TRUE;

  public function getConvertFileShell($source, &$destination) {
    return array(
      $this->cmd,
      Shell::arg('form', Shell::SHELL_ARG_BASIC_DBL_NOEQUAL, 'from='
        . $this->conversion[0]),
      Shell::arg('form', Shell::SHELL_ARG_BASIC_DBL_NOEQUAL, 'to='
        . $this->conversion[1]),
      Shell::arg('form', Shell::SHELL_ARG_BASIC_DBL_NOEQUAL, 'input_files[]=@'
        . $source),
      'http://c.docverter.com/convert',
      Shell::arg('>', Shell::SHELL_SAFE),
      $destination,
    );
  }

  protected function getHelpInstallation($os, $os_version) {
    $help = array(
      'title' => 'Docverter',
      'url' => 'http://www.docverter.com/',
    );
    switch ($os) {
      case 'Ubuntu':
        $help['os'] = 'confirmed on Ubuntu 12.04';
        $help['apt-get'] = 'curl';
        return $help;
    }

    return parent::getHelpInstallation($os, $os_version);
  }

  public function getVersionInfo() {
    return array(
      'docverter' => 'REST',
    );
  }


  public function isAvailable() {
    $this->cmd = $this->shellWhich('curl');
    return isset($this->cmd);
  }
}