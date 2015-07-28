<?php
/*
 * This file is part of the FileConverter package.
 *
 * (c) Greg Payne
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FileConverter\Engine\Convert;

use FileConverter\Engine\EngineBase;
class PdfGrayscale extends EngineBase {
  public function getConvertFileShell($source, &$destination) {
    // Snippet from:
    // http://unix.stackexchange.com/questions/93959/how-to-convert-a-color-pdf-to-black-white
    return array(
      'gs',
      "-sOutputFile=$destination",
      "-sDEVICE=pdfwrite",
      "-sColorConversionStrategy=Gray",
      "-dProcessColorModel=/DeviceGray",
      "-dCompatibilityLevel=1.4",
      "-dNOPAUSE",
      "-dBATCH",
      $source,
    );
  }

  protected function getHelpInstallation($os, $os_version) {
    $help = array(
      'title' => 'GhostScript',
      'url' => 'http://www.ghostscript.com/',
    );
    switch ($os) {
      case 'Ubuntu':
        $help['os'] = 'confirmed on Ubuntu 12.04';
        $help['apt-get'] = 'ghostscript';
        return $help;
    }

    return parent::getHelpInstallation($os, $os_version);
  }

  public function getVersionInfo() {
    $gsv = $this->shell('gs -v');
    if (preg_match('@Ghostscript ([0-9\.]+)@s', $gsv, $arr)) {
      return array(
        'gs' => $arr[1],
      );
    }
    else {
      return array(
        'gs' => 'UNKNOWN',
      );
    }
  }

  public function isAvailable() {
    $this->cmd = $this->shellWhich('gs');
    return isset($this->cmd);
  }

}