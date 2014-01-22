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
  public function getConvertFileShell($source, &$destination) {
    return array(
      $this->cmd,
      '--printToFile',
      $source,
      $destination,
    );
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

  public function getVersionInfo() {
    $info = array(
        'ted' => 'unknown',
    );
    $v = $this->shell($this->cmd . " --version");
    if (preg_match('@Ted ([\d\.]+)@', $v, $arr)) {
      $info['ted'] = $arr[1];
    }
    return $info;
  }

  public function isAvailable() {
    $this->cmd = $this->shellWhich('Ted');
    return isset($this->cmd);
  }

}