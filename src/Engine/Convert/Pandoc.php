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
class Pandoc extends EngineBase {
  protected $cmd_options = array(
    array(
      'name' => 'from',
      'description' => 'Provide a pandoc format constant (e.g., markdown, markdown_github, etc.)',
      'mode' => Shell::SHELL_ARG_BASIC_DBL,
    ),
    array(
      'name' => 'to',
      'description' => 'Provide a pandoc format constant (e.g., markdown, markdown_github, etc.)',
      'mode' => Shell::SHELL_ARG_BASIC_DBL,
    ),
    array(
      'name' => 'data-dir',
      'description' => 'Pandoc user data directory, defaults to $HOME/.pandoc',
      'mode' => Shell::SHELL_ARG_BASIC_DBL,
    ),
  );
  public function getConvertFileShell($source, &$destination) {
    return array(
      $this->cmd['pandoc'],
      Shell::argOptions($this->cmd_options, $this->configuration),
      $source,
      Shell::arg('o', Shell::SHELL_ARG_BASIC_SGL, $destination),
    );
  }

  protected function getHelpInstallation($os, $os_version) {
    $help = array(
      'title' => 'Pandoc',
      'url' => 'http://johnmacfarlane.net/pandoc/',
    );
    switch ($os) {
      case 'Ubuntu':
        $help['os'] = 'confirmed on Ubuntu 12.04';
        $help['apt-get'] = array(
          'pandoc',
          'texlive',
        );
        return $help;
    }

    return parent::getHelpInstallation($os, $os_version);
  }

  public function getVersionInfo() {
    $latex = basename($this->cmd['latex']);
    $info = array(
      'pandoc' => 'UNKNOWN',
      $latex => 'UNKNOWN',
    );
    $v = $this->shell(array(
      $this->cmd['pandoc'],
      '-v'
    ));
    if (preg_match("@pandoc ([\d\.]+)@s", $v, $arr)) {
      $info['pandoc'] = $arr[1];
    }
    $v = $this->shell(array(
      $this->cmd['latex'],
      '-v'
    ));
    if (preg_match("@pdfTeX ([\d\.\-]+)@s", $v, $arr)) {
      $info[$latex] = $arr[1];
    }
    return $info;
  }

  public function isAvailable() {
    $this->cmd = array(
      'pandoc' => $this->shellWhich('pandoc'),
      'latex' => $this->shellWhich('pdflatex'),
    );
    return isset($this->cmd['pandoc']) && isset($this->cmd['latex']);
  }
}