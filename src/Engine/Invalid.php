<?php
/*
 * This file is part of the Witti FileConverter package.
 *
 * (c) Greg Payne
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Witti\FileConverter\Engine;

class Invalid extends EngineBase {
  public function convertFile($source, $destination) {
    return $this;
  }

  public function getHelpInstallation($os, $os_version) {
    return "Invalid engine configuration provided.";
  }

  public function isAvailable() {
    return FALSE;
  }

}