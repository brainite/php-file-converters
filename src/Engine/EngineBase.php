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

use FileConverter\FileConverter;
use FileConverter\Util\Shell;
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

  public function __construct(FileConverter &$converter, $convert_path, $settings, $configuration) {
    $this->converter =& $converter;
    $this->conversion = explode('->', strtolower($convert_path), 2);
    if (!isset($this->conversion[1])) {
      $this->conversion[1] = preg_replace('@^.*[~]@s', '', $convert_path);
    }
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
          . preg_replace('@/.*$@', '', $this->conversion[1]), $s_path);
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
      if (!$source_safe && $s_path !== $d_path) {
        unlink($s_path);
      }

      // Throw an exception if the destination was not created.
      if (!is_file($d_path)) {
        throw new \ErrorException($output);
      }

      // Move the converted temporary file to the destination.
      rename($d_path, $destination);
      chmod($destination, 0644);
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

  public function getConfiguration() {
    return $this->configuration;
  }

  public function getHelp($type = 'installation') {
    $os = $this->settings['operating_system'];
    $os_version = $this->settings['operating_system_version'];
    switch ($type) {
      case 'installation':
        $help = $this->getHelpInstallation($os, $os_version);
        if (isset($help)) {
          if (!is_string($help)) {
            $help = var_export($help, 1);
          }
        }
        return $help;
    }

    return '';
  }

  protected function getHelpInstallation($os, $os_version) {
    return array(
      'title' => "Unable to confirm installation instructions for OS='$os' Ver='$os_version'",
    );
  }

  public function getTempFile($file_extension = NULL) {
    $dir = $this->settings['temp_dir'];
    $tmp = tempnam($dir, 'file-converter-');
    if (isset($file_extension)) {
      $ret = $tmp . '.' . preg_replace('@/.*$@', '', $file_extension);
      rename($tmp, $ret);
      return $ret;
    }
    return $tmp;
  }

  public function getVersionInfo() {
    return array();
  }

  abstract public function isAvailable();

  public function shell($command) {
    $cmd = "";
    $stderr = NULL;
    if (is_string($command)) {
      $cmd = $command;
    }
    else {
      foreach ($command as $part) {
        if ($part instanceof Shell) {
          $cmd .= ' ' . $part->render();
          if ($part->getMode() === Shell::SHELL_STDERR) {
            $stderr = $part->getValue();
          }
        }
        else {
          $cmd .= ' ' . escapeshellarg($part);
        }
      }
    }
    if (!isset($stderr)) {
      $cmd .= ' 2>&1 ';
    }

    if (function_exists('drush_get_context')
      && drush_get_context('DRUSH_VERBOSE')) {
      drush_print(dt('SHELL: !cmd', array(
        '!cmd' => $cmd,
      )));
    }

    // Get the output. Concat the stderr info, if required.
    $output = trim(shell_exec($cmd));
    if (isset($stderr) && is_file($stderr)) {
      if ($output !== '') {
        $output .= "\n";
      }
      $output .= file_get_contents($stderr);
      unlink($stderr);
    }
    return $output;
  }

  public function shellWhich($command) {
    static $cache = array();
    if (array_key_exists($command, $cache)) {
      return $cache[$command];
    }

    // Look in the bin folder (symlinks work fine).
    $bin = realpath(__DIR__ . '/../../bin/' . $command);
    if ($bin !== FALSE && is_executable($bin)) {
      $cache[$command] = $bin;
      return $bin;
    }

    // Use the which/where command to locate the binary.
    $which = preg_match('@Win@', $this->settings['operating_system']) ? 'where'
      : 'which';
    $path = $this->shell(array(
      $which,
      $command
    ));
    if (!$path || !is_file($path) || !is_executable($path)) {
      $path = NULL;
    }
    $cache[$command] = $path;
    return $path;
  }

}