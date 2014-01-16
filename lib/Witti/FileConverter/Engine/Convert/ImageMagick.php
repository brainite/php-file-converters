<?php
namespace Witti\FileConverter\Engine\Convert;

use Witti\FileConverter\Engine\EngineBase;
use Witti\FileConverter\Util\Shell;
class ImageMagick extends EngineBase {
  protected $cmd_options = array(
    array(
      'name' => 'resize',
      'mode' => Shell::SHELL_ARG_BASIC_SGL,
      'default' => NULL,
      'group' => 1,
    ),
    array(
      'name' => 'colorspace',
      'mode' => Shell::SHELL_ARG_BASIC_SGL,
      'default' => NULL,
      'group' => 1,
    ),
    array(
      'name' => 'alpha',
      'mode' => Shell::SHELL_ARG_BASIC_SGL,
      'default' => NULL,
      'group' => 1,
    ),
    array(
      'name' => 'quality',
      'mode' => Shell::SHELL_ARG_BASIC_SGL,
      'default' => NULL,
      'group' => 1,
    ),
    array(
      'name' => 'flatten',
      'mode' => Shell::SHELL_ARG_BOOL_SGL,
      'default' => NULL,
      'group' => 1,
    ),
  );

  public function convertFile($source, $destination) {
    // Work with temporary files.
    $s_path = $this->getTempFile($this->conversion[0]);
    $d_path = str_replace('.' . $this->conversion[0], '.dest.'
      . $this->conversion[1], $s_path);
    copy($source, $s_path);

    // Convert the temporary file to the destination extension.
    $shell = array();
    $shell[] = $this->cmd;
    $shell[] = Shell::argOptions($this->cmd_options, $this->configuration, 1);
    $shell[] = $s_path;
    $shell[] = $d_path;
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
    $output = "ImageMagick is maintained at http://www.imagemagick.org/\n";
    switch ($os) {
      case 'Ubuntu':
        $output .= " Ubuntu (12.04)\n";
        $output .= "  sudo apt-get install imagemagick\n";
        return $output;
    }

    return $output .= parent::getHelpInstallation();
  }

  public function isAvailable() {
    $this->cmd = $this->shellWhich('convert');
    return isset($this->cmd);
  }
}