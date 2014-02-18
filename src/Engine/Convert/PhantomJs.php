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
class PhantomJs extends EngineBase {
  protected $cmd_options = array(
    array(
      'name' => 'ignore-ssl-errors',
      'mode' => Shell::SHELL_ARG_BASIC_DBL,
      'default' => 'no',
      'group' => 1,
    ),
    array(
      'name' => 'output-width',
      'default' => '8.5in',
      'group' => 'rasterize',
    ),
    array(
      'name' => 'output-height',
      'default' => '11in',
      'group' => 'rasterize',
    ),
    array(
      'name' => 'output-zoom',
      'default' => 1,
      'group' => 'rasterize',
    ),
  );
  protected $cmd_source_safe = TRUE;

  public function getConvertFileShell($source, &$destination) {
    $conf = $this->configuration;
    foreach ($this->cmd_options as $opt) {
      if ($opt['group'] === 'rasterize' && !isset($conf[$opt['name']])) {
        $conf[$opt['name']] = $opt['default'];
      }
    }
    $dimensions = $conf['output-width'] . '*' . $conf['output-height'];
    $zoom = 1;
    $shell = array(
      $this->cmd,
      Shell::argOptions($this->cmd_options, $this->configuration, 1),
      realpath(__DIR__ . '/Resources/PhantomJs-rasterize.js'),
      'file://' . $source,
      $destination,
      $dimensions,
      $zoom,
    );
    return $shell;
  }

  protected function getHelpInstallation($os, $os_version) {
    $help = array(
      'title' => 'PhantomJs',
      'url' => 'http://phantomjs.org/',
    );
    switch ($os) {
      case 'Ubuntu (12.04 LTS)':
        $help['os'] = 'confirmed on Ubuntu 12.04';
        $help['apt-get'] = 'phantomjs xvfb';
        $help['notes'] = array(
          "Ubuntu 12.04 runs version 1.4, which still requires xvfb.",
          "Alternately, download a no-xvfb binary from http://phantomjs.org/download.html",
        );
        return $help;
    }

    return parent::getHelpInstallation($os, $os_version);
  }

  public function getVersionInfo() {
    $info = array(
      'phantomjs' => $this->shell($this->cmd . " --version")
    );
    return $info;
  }

  public function isAvailable() {
    $this->cmd = $this->shellWhich('phantomjs');
    return isset($this->cmd);
  }
}