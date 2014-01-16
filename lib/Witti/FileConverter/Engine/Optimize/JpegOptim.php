<?php
namespace Witti\FileConverter\Engine\Optimize;

use Witti\FileConverter\Engine\EngineBase;
use Witti\FileConverter\Util\Shell;
class JpegOptim extends EngineBase {
  protected $cmd_options = array(
    array(
      'name' => 'quiet',
      'mode' => Shell::SHELL_ARG_BOOL_DBL,
      'default' => FALSE,
    ),
    array(
      'name' => 'strip-all',
      'mode' => Shell::SHELL_ARG_BOOL_DBL,
      'default' => FALSE,
    ),
  );

  public function convertFile($source, $destination) {
    if (!isset($this->cmd)) {
      return FALSE;
    }

    // Work with temporary files.
    $ext = preg_replace('@~.*$@', '', $this->conversion[0]);
    $s_path = $this->getTempFile($ext);
    copy($source, $s_path);

    // Convert the temporary file to the destination extension.
    $shell = array();
    $shell[] = $this->cmd;
    $shell[] = Shell::argOptions($this->cmd_options, $this->configuration, NULL);
    $shell[] = $s_path;
    $output = $this->shell($shell);

    // Move the converted temporary file to the destination.
    if (!is_file($s_path)) {
      echo $output . "\n";
      return FALSE;
    }
    rename($s_path, $destination);
  }

  public function getHelpInstallation($os, $os_version) {
    $output = "JpegOptim is maintained at http://freecode.com/projects/jpegoptim\n";
    switch ($os) {
      case 'Ubuntu':
        $output .= " Ubuntu (12.04)\n";
        $output .= "  sudo apt-get install jpegoptim\n";
        return $output;
    }

    return $output .= parent::getHelpInstallation();
  }

  public function isAvailable() {
    $this->cmd = $this->shellWhich('jpegoptim');
    return isset($this->cmd);
  }
}