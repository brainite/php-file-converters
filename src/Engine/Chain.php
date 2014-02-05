<?php
namespace Witti\FileConverter\Engine;

class Chain extends EngineBase {
  public function convertFile($source, $destination) {
    $links = explode('->', $this->configuration['chain']);

    $s_path = $this->getTempFile(array_shift($links));
    copy($source, $s_path);
    while (!empty($links)) {
      try {
        $d_path = $this->getTempFile(array_shift($links));
        $this->converter->convertFile($s_path, $d_path);
        unlink($s_path);
        if (!is_file($d_path)) {
          throw new \ErrorException("Conversion failed.");
        }
        $s_path = $d_path;
      } catch (\Exception $e) {
        unlink($s_path);
        if (is_file($d_path)) {
          unlink($d_path);
        }
        throw $e;
      }
    }

    rename($d_path, $destination);
    return $this;
  }

  public function getHelpInstallation($os, $os_version) {
    return "Utilizing the 'Chain' engine is installed, but it requires intermediate engines.";
  }

  public function isAvailable() {
    return TRUE;
  }
}