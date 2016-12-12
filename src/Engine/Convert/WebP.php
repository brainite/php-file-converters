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
use FileConverter\Util\Shell;
class WebP extends EngineBase {
  public function getConvertFileShell($source, &$destination) {
    if ($this->conversion[1] === 'webp') {
      $this->cmd = $this->shellWhich('cwebp');
      return array(
        $this->cmd,
        //         '-lossless',
        $source,
        '-o',
        $destination,
      );
    }
    else {
      return array(
        $this->cmd,
        $source,
        '-o',
        $destination,
      );
    }
  }

  protected function getHelpInstallation($os, $os_version) {
    $help = array(
      'title' => 'WebP',
      'url' => 'https://developers.google.com/speed/webp/docs/using',
    );
    switch ($os) {
      case 'Ubuntu':
        $help['os'] = 'confirmed on Ubuntu 16.04';
        $help['apt-get'] = 'webp';
        return $help;
    }

    return parent::getHelpInstallation($os, $os_version);
  }

  public function getVersionInfo() {
    $info = array(
      'dwebp' => $this->shell('dwebp -version'),
    );
    return $info;
  }

  public function isAvailable() {
    $this->cmd = $this->shellWhich('dwebp');
    return isset($this->cmd);
  }
}