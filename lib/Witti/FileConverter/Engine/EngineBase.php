<?php
namespace Witti\FileConverter\Engine;

use Witti\FileConverter\FileConverter;
use Witti\FileConverter\Util\Shell;
/**
 * Each engine must implement at least one of:
 *   1. $this convertFile($s, $d)
 *   2. $this convertString($s, &$d)
 *   3. array getConvertFileShell($s, &$d)
 */
abstract class EngineBase {
  /**
   * Reference the original file converter.
   * @var FileConverter
   */
  protected $converter = NULL;
  protected $conversion = array(
    'null',
    'null'
  );
  protected $settings = array();
  protected $configuration = array();
  protected $cmd = NULL;
  protected $cmd_options = array();
  protected $cmd_source_safe = FALSE;

  public function __construct(FileConverter $converter, $convert_path, $settings, $configuration) {
    $this->conversion = explode('->', strtolower($convert_path), 2);
    $this->settings = $settings;
    $this->configuration = $configuration;
  }

  public function convertFile($source, $destination) {
    // Handle special cases.
    if (method_exists($this, 'getConvertFileShell')) {
      if (!isset($this->cmd)) {
        throw new \ErrorException("The engine's shell command is not available.");
      }

      // Get the base converter object.
      // Get a temporary file with the source extension since libre does not accept an output file name.
      $source_safe = $this->cmd_source_safe;
      if ($source_safe) {
        $s_path = $source;
        $d_path = $this->getTempFile($this->conversion[1]);
      }
      else {
        $s_path = $this->getTempFile($this->conversion[0]);
        $d_path = str_replace('.' . $this->conversion[0], '.dest.'
          . $this->conversion[1], $s_path);
      }

      // Get the command.
      $cmd = $this->getConvertFileShell($s_path, $d_path);
      if (!is_array($cmd) || empty($cmd)) {
        throw new \ErrorException("Invalid configuration for engine.");
      }
      if (!$source_safe) {
        copy($source, $s_path);
      }

      // Convert the temporary file to the destination extension.
      $output = $this->shell($cmd);

      // Remove the original temporary file.
      if (!$source_safe) {
        unlink($s_path);
      }

      // Throw an exception if the destination was not created.
      if (!is_file($d_path)) {
        throw new \ErrorException($output);
      }

      // Move the converted temporary file to the destination.
      rename($d_path, $destination);
      return $this;
    }
    else {
      // Read the string, convert it, and write it.
      $source_string = file_get_contents($source);
      $destination_string = '';
      $this->convertString($source_string, $destination_string);
      file_put_contents($destination, $destination_string);

      return $this;
    }
  }

  public function convertString($source, &$destination) {
    // Stop quickly for the trivial conversion.
    if ($this->conversion[0] === $this->conversion[1]) {
      $destination = $source;
      return $this;
    }

    // Create temp source and destination files.
    $s_path = $this->getTempFile($this->conversion[0]);
    $d_path = $this->getTempFile($this->conversion[1]);

    // Convert the string.
    file_put_contents($s_path, $source);
    $this->convertFile($s_path, $d_path);
    $destination = file_get_contents($d_path);

    // Remove the files.
    unlink($s_path);
    unlink($d_path);

    return $this;
  }

  public function getHelp($type = 'installation') {
    $os = $this->settings['operating_system'];
    $os_version = $this->settings['operating_system_version'];
    switch ($type) {
      case 'installation':
        return $this->getHelpInstallation($os, $os_version);
    }

    return '';
  }

  public function getHelpInstallation($os = NULL, $os_version = NULL) {
    if (isset($os)) {
      return "No installation instructions available.";
    }
    else {
      return "No installation instructions available for your platform.";
    }
  }

  public function getTempFile($file_extension = NULL) {
    $dir = $this->settings['temp_dir'];
    $tmp = tempnam($dir, 'file-converter-');
    if (isset($file_extension)) {
      rename($tmp, $tmp . '.' . $file_extension);
      return $tmp . '.' . $file_extension;
    }
    return $tmp;
  }

  public function getVersionInfo() {
    return array();
  }

  abstract public function isAvailable();

  public function shell($command) {
    $cmd = "";
    if (is_string($command)) {
      $cmd = $command;
    }
    else {
      foreach ($command as $part) {
        if ($part instanceof Shell) {
          $cmd .= ' ' . $part->render();
        }
        else {
          $cmd .= ' ' . escapeshellarg($part);
        }
      }
    }
    $cmd .= ' 2>&1 ';

//     var_dump($cmd);
    return trim(shell_exec($cmd));
  }

  public function shellWhich($command) {
    $which = preg_match('@Win@', $this->settings['operating_system']) ? 'where'
      : 'which';
    $path = $this->shell(array(
      $which,
      $command
    ));
    if (!$path || !is_file($path) || !is_executable($path)) {
      $path = NULL;
    }
    return $path;
  }

}