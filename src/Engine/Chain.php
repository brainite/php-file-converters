<?php
/*
 * This file is part of the FileConverter package.
 *
 * (c) Greg Payne
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FileConverter\Engine;

class Chain extends EngineBase {
  public function convertFile($source, $destination) {
    $links = explode('->', $this->configuration['chain']);

    // Allow wildcards for the start/end
    if ($links[0] === '*') {
      $links[0] = $this->conversion[0];
    }
    if ($links[sizeof($links) - 1] === '*') {
      $links[sizeof($links) - 1] = $this->conversion[1];
    }

    // Iterate through the chain.
    $c0 = array_shift($links);
    $s_path = $this->getTempFile($c0);
    copy($source, $s_path);
    while (!empty($links)) {
      try {
        $c1 = array_shift($links);
        if (empty($links)) {
          $d_path = $destination;
        }
        else {
          $d_path = $this->getTempFile($c1);
        }
        $this->converter->convertFile($s_path, $d_path, "$c0->$c1");
        unlink($s_path);
        if (!is_file($d_path) && !is_dir($d_path)) {
          throw new \ErrorException("Conversion failed.");
        }
        $s_path = $d_path;
        $c0 = $c1;
      } catch (\Exception $e) {
        if (is_file($s_path) && $s_path !== $source) {
          unlink($s_path);
        }
        if (is_file($d_path) && $d_path !== $destination) {
          unlink($d_path);
        }
        throw $e;
      }
    }
    return $this;
  }

  protected function getHelpInstallation($os, $os_version) {
    return array(
      'title' => 'Chain Engine',
      'notes' => array(
        "Utilizing the 'Chain' engine is installed, but it requires intermediate engines.",
      ),
    );
  }

  public function isAvailable() {
    return TRUE;
  }
}