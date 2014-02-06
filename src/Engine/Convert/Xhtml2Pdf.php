<?php
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

  public function getHelpInstallation($os, $os_version) {
    $output = "Xhtml2Pdf can be found online at http://www.xhtml2pdf.com/\n";
    switch ($os) {
      case 'Ubuntu':
        $output .= " Ubuntu (12.04)\n";
        $output .= "  sudo apt-get install python-pisa\n";
        return $output;
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