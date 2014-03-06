<?php
/*
 * This file is part of the FileConverter package.
 *
 * (c) Greg Payne
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Witti\FileConverter\Engine\Convert;
use Witti\FileConverter\Engine\EngineBase;
use Witti\FileConverter\Util\Shell;
class Mpdf extends EngineBase {
  public function convertFile($source, $destination) {
    $html = file_get_contents($source);
    $mpdf = new \mPDF();
    $mpdf->WriteHTML($html);
    $destination = $mpdf->Output($destination, 'F');
  }

  public function convertString($source, &$destination) {
    $mpdf = new \mPDF();
    $mpdf->WriteHTML($source);
    $destination = $mpdf->Output('', 'S');
  }

  protected function getHelpInstallation($os, $os_version) {
    $help = array(
      'title' => 'mPdf',
      'url' => 'http://www.mpdf1.com/',
    );
    switch ($os) {
      case 'Ubuntu':
        $help['os'] = 'confirmed on Ubuntu 12.04';
        $help['notes'] = array(
          'composer update',
        );
        return $help;
    }

    return parent::getHelpInstallation($os, $os_version);
  }

  public function getVersionInfo() {
    return array(
      'mpdf' => mPDF_VERSION,
    );
  }

  public function isAvailable() {
    return (class_exists('mPDF'));
  }

}