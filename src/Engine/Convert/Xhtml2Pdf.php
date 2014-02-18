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
use Witti\FileConverter\Util\Shell;
class Xhtml2Pdf extends EngineBase {
  protected $cmd_options = array(
    array(
      'name' => 'quiet',
      'mode' => Shell::SHELL_ARG_BOOL_DBL,
      'default' => TRUE,
    ),
    array(
      'name' => 'css-dump',
      'mode' => Shell::SHELL_ARG_BOOL_DBL,
    ),
    array(
      'name' => 'debug',
      'mode' => Shell::SHELL_ARG_BOOL_DBL,
    ),
    array(
      'name' => 'warn',
      'mode' => Shell::SHELL_ARG_BOOL_DBL,
    ),
    array(
      'name' => 'xhtml',
      'description' => 'Same as --xml, opposite of --html (implied arg)',
      'mode' => Shell::SHELL_ARG_BOOL_DBL,
      'default' => FALSE,
    ),
    array(
      'name' => 'base',
      'description' => 'Base path',
      'mode' => Shell::SHELL_ARG_BASIC_DBL,
    ),
    array(
      'name' => 'css',
      'description' => 'CSS File',
      'mode' => Shell::SHELL_ARG_BASIC_DBL,
    ),
    array(
      'name' => 'encoding',
      'description' => 'Character encoding',
      'mode' => Shell::SHELL_ARG_BASIC_DBL,
    ),
  );

  public function getConvertFileShell($source, &$destination) {
    return array(
      $this->cmd,
      Shell::argOptions($this->cmd_options, $this->configuration),
      $source,
      $destination,
    );
  }

  protected function getHelpInstallation($os, $os_version) {
    $help = array(
      'title' => 'Xhtml2Pdf',
      'url' => 'http://www.xhtml2pdf.com/',
    );
    switch ($os) {
      case 'Ubuntu':
        $help['os'] = 'confirmed on Ubuntu 12.04';
        $help['apt-get'] = 'python-pisa';
        return $help;
    }

    return parent::getHelpInstallation($os, $os_version);
  }

  public function getVersionInfo() {
    $info = array(
      'xhtml2pdf' => $this->shell($this->cmd . " --version")
    );
    if (preg_match('@pisa ([\d\.]+)@', $info['xhtml2pdf'], $arr)) {
      $info['xhtml2pdf'] = $arr[1];
    }
    return $info;
  }

  public function isAvailable() {
    $this->cmd = $this->shellWhich('xhtml2pdf');
    return isset($this->cmd);
  }
}