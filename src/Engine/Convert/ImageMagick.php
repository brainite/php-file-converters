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

  public function getConvertFileShell($source, &$destination) {
    $multipage = array('pdf');
    if (in_array($this->conversion[0], $multipage)) {
      if (!in_array($this->conversion[1], $multipage)) {
        $source .= '[0]';
      }
    }
    return array(
      $this->cmd,
      Shell::argOptions($this->cmd_options, $this->configuration, 1),
      $source,
      $destination,
    );
  }

  public function getHelpInstallation($os, $os_version) {
    $output = "ImageMagick is maintained at http://www.imagemagick.org/\n";
    switch ($os) {
      case 'Ubuntu':
        $output .= " Ubuntu (12.04)\n";
        $output .= "  sudo apt-get install imagemagick\n";
        return $output;
    }

    return $output .= parent::getHelpInstallation($os, $os_version);
  }

  public function isAvailable() {
    $this->cmd = $this->shellWhich('convert');
    return isset($this->cmd);
  }
}