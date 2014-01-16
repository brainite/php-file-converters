<?php
/**
 * @file
 * Integrate with Ted.
 *
 * @link http://www.nllgg.nl/Ted/
 */

namespace Witti\FileConverter\Engine\Convert;

use Witti\FileConverter\Engine\EngineBase;
class Ted extends EngineBase {
  private $scripts = array(
    'rtf2pdf' => NULL,
  );

  public function convertFile($source, $destination) {
    if (!isset($this->cmd)) {
      return FALSE;
    }

    // Get the base converter object.
    // Get a temporary file with the source extension since libre does not accept an output file name.
    $s_path = $this->getTempFile($this->conversion[0]);
    $d_path = str_replace('.' . $this->conversion[0], '.'
      . $this->conversion[1], $s_path);
    copy($source, $s_path);

    // Convert the temporary file to the destination extension.
    $output = $this->shell(array(
      $this->cmd,
      '--printToFile',
      $s_path,
      $d_path,
    ));
    if (!is_file($d_path)) {
      echo $output . "\n";
    }

    // Remove the original temporary file.
    unlink($s_path);
    // Move the converted temporary file to the destination.
    rename($d_path, $destination);
  }

  public function getHelpInstallation($os, $os_version) {
    switch ($os) {
      case 'Ubuntu':
        $output = array(
          'Download .deb file from http://www.nllgg.nl/Ted/#How_to_install_Ted',
          'sudo dpkg -i <package-details>.deb',
          'Ex: sudo dpkg -i ted-2.23-amd64.deb',
        );
        return join("\n", $output);
    }

    return parent::getHelpInstallation();
  }

  public function isAvailable() {
    $this->cmd = $this->shellWhich('Ted');
    return isset($this->cmd);
  }

}