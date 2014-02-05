<?php
namespace Witti\FileConverter\Engine\Optimize;

use Witti\FileConverter\Engine\EngineBase;
/**
 * @todo Add support for field replacement by creating xfdf XML that pdftk can use.
 * @link http://partners.adobe.com/public/developer/en/xml/XFDF_Spec_3.0.pdf
 *
 */
class Pdftk extends EngineBase {
  public function convertFile($source, $destination) {
    // One convert function is required to avoid recursion.
    // pdftk source.pdf dump_data | sed 's/Value: .*/Value:/' > empty.txt
    // pdftk source.pdf update_info empty.txt output destination.pdf flatten compress

    return $this;
  }

  public function getHelpInstallation($os, $os_version) {
    return "This engine is a placeholder and is not yet ready for use.";

    switch ($os) {
      case 'Ubuntu':
        return "sudo apt-get install pdftk";
    }

    return parent::getHelpInstallation();
  }

  public function isAvailable() {
    return FALSE;

    $this->cmd = $this->shellWhich('pdftk');
    return isset($this->cmd);
  }
}