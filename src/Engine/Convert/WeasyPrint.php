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
class WeasyPrint extends EngineBase {
  /**
   * @link https://doc.courtbouillon.org/weasyprint/stable/api_reference.html
   * @var array
   */
  protected $cmd_options = array(
    array(
      'name' => 'quiet',
      'mode' => Shell::SHELL_ARG_BOOL_DBL,
      'default' => TRUE,
    ),
    array(
      'name' => 'debug',
      'mode' => Shell::SHELL_ARG_BOOL_DBL,
    ),
    array(
      'name' => 'srgb',
      'mode' => Shell::SHELL_ARG_BOOL_DBL,
    ),
    array(
      'name' => 'optimize-images',
      'mode' => Shell::SHELL_ARG_BOOL_DBL,
    ),
    array(
      'name' => 'timeout',
      'mode' => Shell::SHELL_ARG_BASIC_DBL,
    ),
    array(
      'name' => 'media-type',
      'description' => 'Media type to use (defaults to print)',
      'mode' => Shell::SHELL_ARG_BASIC_DBL,
    ),
    array(
      'name' => 'base-url',
      'description' => 'Base path',
      'mode' => Shell::SHELL_ARG_BASIC_DBL,
    ),
    array(
      'name' => 'attachment',
      'description' => 'Attachment',
      'mode' => Shell::SHELL_ARG_BASIC_DBL,
    ),
    array(
      'name' => 'pdf-variant',
      'description' => 'PDF Variant',
      'mode' => Shell::SHELL_ARG_BASIC_DBL,
    ),
    array(
      'name' => 'pdf-version',
      'description' => 'PDF Version Number',
      'mode' => Shell::SHELL_ARG_BASIC_DBL,
    ),
    array(
      'name' => 'dpi',
      'description' => 'DPI',
      'mode' => Shell::SHELL_ARG_BASIC_DBL,
    ),
    array(
      'name' => 'stylesheet',
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
      'title' => 'WeasyPrint',
      'url' => 'https://doc.courtbouillon.org/weasyprint/stable/index.html',
    );
    switch ($os) {
      case 'Ubuntu':
      case 'Debian':
        $help['os'] = 'confirmed on Debian 12.8';
        $help['apt-get'] = 'weasyprint';
        return $help;
    }

    return parent::getHelpInstallation($os, $os_version);
  }

  public function getVersionInfo() {
    $info = array(
      'weasyprint' => $this->shell($this->cmd . " --version")
    );
    if (preg_match('@version ([\d\.]+)@', $info['weasyprint'], $arr)) {
      $info['weasyprint'] = $arr[1];
    }
    return $info;
  }

  public function isAvailable() {
    $this->cmd = $this->shellWhich('weasyprint');
    return isset($this->cmd);
  }
}
