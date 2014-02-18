<?php
/*
 * This file is part of the Witti FileConverter package.
 *
 * (c) Greg Payne
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

    // Use sed to replace the ID with a new MD5 that will not vary.
    // /ID [<e8c87bb7a19df73c042da3b2a01dc7ff> <e8c87bb7a19df73c042da3b2a01dc7ff>]

    return $this;
  }

  protected function getHelpInstallation($os, $os_version) {
    $help = array(
      'title' => 'PdfTk',
    );
    switch ($os) {
      case 'Ubuntu':
        $help['os'] = 'confirmed on Ubuntu 12.04';
        $help['apt-get'] = 'pdftk';
        return $help;
    }

    return parent::getHelpInstallation($os, $os_version);
  }

  public function isAvailable() {
    return FALSE;

    $this->cmd = $this->shellWhich('pdftk');
    return isset($this->cmd);
  }
}