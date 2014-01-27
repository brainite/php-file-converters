<?php
namespace Witti\FileConverter\Engine\Convert;

use Witti\FileConverter\Engine\EngineBase;
use Witti\FileConverter\Util\Shell;
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
  );

  public function getConvertFileShell($source, &$destination) {
    return array(
      $this->cmd,
      Shell::argOptions($this->cmd_options, $this->configuration),
      $source,
      $destination,
    );
  }

  public function getHelpInstallation($os, $os_version) {
    $output = "wkhtmltopdf can be found online at https://code.google.com/p/wkhtmltopdf/\n";
    switch ($os) {
      case 'Ubuntu':
        $output .= " Ubuntu (12.04)\n";
        $output .= "  sudo apt-get install wkhtmltopdf\n";
        return $output;
    }

    return parent::getHelpInstallation();
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
    $bin = realpath(__DIR__ . '/../../../../../bin');
    if (is_dir($bin) && is_file("$bin/wkhtmltopdf-0.11.0rc1-amd64")) {
      $this->cmd = "$bin/wkhtmltopdf-0.11.0rc1-amd64";
    }
    else {
      $this->cmd = $this->shellWhich('wkhtmltopdf');
    }

    return isset($this->cmd);
  }
}