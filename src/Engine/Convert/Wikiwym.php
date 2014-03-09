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

use FileConverter\Util\Shell;
class Wikiwym extends PhantomJs {
  public function getConvertFileShell($source, &$destination) {
    $shell = array(
      $this->cmd,
      realpath(__DIR__ . '/Resources/PhantomJs-wikiwym.js'),
      $source,
      $destination,
    );
    return $shell;
  }

  public function getVersionInfo() {
    $info = array(
      'phantomjs' => $this->shell($this->cmd . " --version"),
      'wikiwym' => 'stable',
    );
    return $info;
  }

}