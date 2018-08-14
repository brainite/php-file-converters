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
class WkHtmlToPdf extends EngineBase {
  protected $cmd_options = array(
    array(
      'name' => 'quiet',
      'mode' => Shell::SHELL_ARG_BOOL_DBL,
      'default' => TRUE,
    ),
    array(
      'name' => 'disable-javascript',
      'mode' => Shell::SHELL_ARG_BOOL_DBL,
    ),
    array(
      'name' => 'no-background',
      'mode' => Shell::SHELL_ARG_BOOL_DBL,
    ),
    array(
      'name' => 'allow',
      'description' => 'Specify a folder whose files can be included',
      'mode' => Shell::SHELL_ARG_MULTIPLE,
      'each' => array(
        'mode' => Shell::SHELL_ARG_BASIC_DBL_NOEQUAL,
      ),
    ),
    array(
      'name' => 'cookie',
      'description' => 'Name:Value for a cookie',
      'mode' => Shell::SHELL_ARG_MULTIPLE,
      'each' => array(
        'mode' => Shell::SHELL_ARG_PAIR_DBL_NOEQUAL,
        'delimiter' => ':',
      ),
    ),
    array(
      'name' => 'cookie-jar',
      'description' => 'Path to a curl-style cookie jar file',
      'mode' => Shell::SHELL_ARG_BASIC_DBL_NOEQUAL,
    ),
    array(
      'name' => 'margin-top',
      'mode' => Shell::SHELL_ARG_BASIC_DBL_NOEQUAL,
      'default' => '10mm',
    ),
    array(
      'name' => 'margin-bottom',
      'mode' => Shell::SHELL_ARG_BASIC_DBL_NOEQUAL,
      'default' => '10mm',
    ),
    array(
      'name' => 'margin-left',
      'mode' => Shell::SHELL_ARG_BASIC_DBL_NOEQUAL,
      'default' => '10mm',
    ),
    array(
      'name' => 'margin-right',
      'mode' => Shell::SHELL_ARG_BASIC_DBL_NOEQUAL,
      'default' => '10mm',
    ),
    array(
      'name' => 'footer-line',
      'description' => 'Display line above the footer',
      'mode' => Shell::SHELL_ARG_BOOL_DBL,
    ),
    array(
      'name' => 'footer-font-size',
      'description' => 'Set footer font size (default 12)',
      'mode' => Shell::SHELL_ARG_BASIC_DBL_NOEQUAL,
    ),
    array(
      'name' => 'footer-html',
      'description' => 'Adds a html footer (must provide URL, not HTML)',
      'mode' => Shell::SHELL_ARG_BASIC_DBL_NOEQUAL,
    ),
    array(
      'name' => 'footer-left',
      'description' => 'Left aligned footer text',
      'mode' => Shell::SHELL_ARG_BASIC_DBL_NOEQUAL,
    ),
    array(
      'name' => 'footer-center',
      'description' => 'Centered footer text',
      'mode' => Shell::SHELL_ARG_BASIC_DBL_NOEQUAL,
    ),
    array(
      'name' => 'footer-right',
      'description' => 'Right aligned footer text',
      'mode' => Shell::SHELL_ARG_BASIC_DBL_NOEQUAL,
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
      'title' => 'WkHtmlToPdf',
      'url' => 'https://code.google.com/p/wkhtmltopdf/',
    );
    switch ($os) {
      case 'Ubuntu':
        $help['os'] = 'confirmed on Ubuntu 12.04';
        $help['apt-get'] = 'wkhtmltopdf';
        return $help;
    }

    return parent::getHelpInstallation($os, $os_version);
  }

  public function getVersionInfo() {
    $info = array(
      'wkhtmltopdf' => $this->shell($this->cmd . " --version")
    );
    if (preg_match('@wkhtmltopdf ([\d\.]+)@', $info['wkhtmltopdf'], $arr)) {
      $info['wkhtmltopdf'] = $arr[1];
    }
    return $info;
  }

  public function isAvailable() {
    $this->cmd = $this->shellWhich('wkhtmltopdf');
    return isset($this->cmd);
  }
}