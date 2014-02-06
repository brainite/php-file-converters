<?php
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

  public function getHelpInstallation($os, $os_version) {
    $help = "Maintained at http://phantomjs.org/\n";
    $help .= "Need to locate rasterize.js\n";
    switch ($os) {
      case 'Ubuntu (12.04 LTS)':
        $help .= "Ubuntu 12.04 runs version 1.4, which still requires xvfb.\n";
        $help .= " sudo apt-get install phantomjs xvfb\n";
        $help .= "Alternately, download a no-xvfb binary from http://phantomjs.org/download.html\n";
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