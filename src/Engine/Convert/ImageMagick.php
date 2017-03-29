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
use FileConverter\Engine\Helper\Archive;
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
    if (preg_match('@(?:zip|directory)/(?<ext>.*)$@s', $this->conversion[1], $arr)) {
      // Create a temp directory and convert the images.
      $ext = $arr['ext'];
      $imageArchive = new Archive($this);
      $imagePath = $imageArchive->getTempDirectory();
      $this->shell(array(
        $this->cmd,
        Shell::argOptions($this->cmd_options, $this->configuration, 1),
        $source,
        "$imagePath/page.$ext",
      ));

      // Create the temp directory
      $archive = new Archive($this);
      $tmp = $archive->getTempDirectory();
      // Rename the multiple image files in a standardized way
      if (is_file("$imagePath/page.$ext")) {
        $path = "$tmp/img/page1.$ext";
        $this->isTempWritable($path);
        rename("$imagePath/page.$ext", $path);
      }
      else {
        $i = 0;
        while (is_file("$imagePath/page-$i.$ext")) {
          $path = "$tmp/img/page" . ($i + 1) . ".$ext";
          $this->isTempWritable($path);
          rename("$imagePath/page-$i.$ext", $path);
          ++$i;
        }
      }
      // Zip the files
      $archive->save($destination);
      return;
    }

    return parent::convertFile($source, $destination);
  }

  public function getConvertFileShell($source, &$destination) {
    $multipage = array(
      'pdf',
    );
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

  protected function getHelpInstallation($os, $os_version) {
    $help = array(
      'title' => 'ImageMagick',
      'url' => 'http://www.imagemagick.org/',
    );
    switch ($os) {
      case 'Ubuntu':
        $help['os'] = 'confirmed on Ubuntu 12.04';
        $help['apt-get'] = 'imagemagick';
        return $help;
    }

    return parent::getHelpInstallation($os, $os_version);
  }

  public function isAvailable() {
    $this->cmd = $this->shellWhich('convert');
    return isset($this->cmd);
  }
}