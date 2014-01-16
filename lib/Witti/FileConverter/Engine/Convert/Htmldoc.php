<?php
namespace Witti\FileConverter\Engine\Convert;

use Witti\FileConverter\Engine\EngineBase;
use Witti\FileConverter\Util\Shell;
class Htmldoc extends EngineBase {
  protected $cmd_options = array(
    array(
      'name' => 'webpage',
      'mode' => Shell::SHELL_ARG_BOOL_DBL,
      'default' => TRUE,
    ),
    array(
      // Ex: 'pdf'
      'name' => 'format',
      'mode' => Shell::SHELL_ARG_BOOL_DBL,
      'default' => NULL,
    ),
    array(
      'name' => 'top',
      'mode' => Shell::SHELL_ARG_BASIC_DBL_NOEQUAL,
      'default' => '1.0in',
    ),
    array(
      'name' => 'bottom',
      'mode' => Shell::SHELL_ARG_BASIC_DBL_NOEQUAL,
      'default' => '1.0in',
    ),
    array(
      'name' => 'left',
      'mode' => Shell::SHELL_ARG_BASIC_DBL_NOEQUAL,
      'default' => '1.0in',
    ),
    array(
      'name' => 'right',
      'mode' => Shell::SHELL_ARG_BASIC_DBL_NOEQUAL,
      'default' => '1.0in',
    ),
    array(
      'name' => 'fontsize',
      'mode' => Shell::SHELL_ARG_BASIC_DBL_NOEQUAL,
      'default' => '10pt',
    ),
    array(
      'name' => 'footer',
      'mode' => Shell::SHELL_ARG_BASIC_DBL_NOEQUAL,
      'default' => '.',
    ),
  );

  public function convertFile($source, $destination) {
    if (!isset($this->cmd)) {
      return FALSE;
    }

    // Work with temporary files.
    $s_path = $this->getTempFile($this->conversion[0]);
    $d_path = str_replace('.' . $this->conversion[0], '.dest.'
      . $this->conversion[1], $s_path);
    copy($source, $s_path);

    // Convert the temporary file to the destination extension.
    $shell = array();
    $shell[] = "cat";
    $shell[] = $s_path;
    $shell[] = Shell::arg('|', Shell::SHELL_SAFE);
    $shell[] = $this->cmd;
    $shell[] = Shell::argOptions($this->cmd_options, $this->configuration, 1);
    $shell[] = Shell::arg('f', Shell::SHELL_ARG_BASIC_SGL, $d_path);
    $shell[] = Shell::arg('-', Shell::SHELL_SAFE);
    $output = $this->shell($shell);
    if (!is_file($d_path)) {
      echo $output . "\n";
    }

    // Remove the original temporary file.
    unlink($s_path);
    // Move the converted temporary file to the destination.
    rename($d_path, $destination);
    return $this;
  }

  public function getHelpInstallation($os, $os_version) {
    switch ($os) {
      case 'Ubuntu':
        return "sudo apt-get install htmldoc";
    }

    return parent::getHelpInstallation();
  }

  public function isAvailable() {
    $this->cmd = $this->shellWhich('htmldoc');
    return isset($this->cmd);
  }
}