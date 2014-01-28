<?php
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