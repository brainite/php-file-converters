<?php
/*
 * This file is part of the FileConverter package.
 *
 * (c) Greg Payne
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @file FileConverter.php
 *
 * Provide an OO point of entry for the File Converter tools.
 */
namespace FileConverter;
use FileConverter\Configuration\ConfigurationDefaults;
use FileConverter\Configuration\ConfigurationOverride;
use FileConverter\Util\FileInfo;

/**
 * The controlling FileConverter class.
 */
class FileConverter {
  /**
   * Get a new converter object, reusing the converter when requested.
   * @param bool $get_singleton
   * @return \FileConverter\FileConverter
   */
  static public function &factory($get_singleton = TRUE) {
    static $singleton = NULL;
    if ($get_singleton) {
      if (!isset($singleton)) {
        $singleton = new FileConverter();
      }
      return $singleton;
    }
    $new = new FileConverter();
    return $new;
  }

  protected $configurations = array();
  protected $settings = array(
    'alias' => array(),
  );
  protected $missing_engines = array();
  protected $previous_engines = array();
  protected $conversion_depth = 0;
  protected $replacements = array(
    'string' => array(),
  );
  public function __construct() {
    $this->configurations[] = new ConfigurationDefaults($this->settings);
  }

  public function convert($type = 'string', $convert_path = 'null->null', $source = '', &$destination = NULL) {
    $conversion_depth = &$this->conversion_depth;
    if ($conversion_depth == 0) {
      $this->previous_engines = array();
    }
    $this->conversion_depth++;

    // Track the files to cleanup.
    $cleanup_files = array();
    $return = function () use (&$cleanup_files, &$conversion_depth) {
      foreach ($cleanup_files as $file) {
        if (is_file($file)) {
          unlink($file);
        }
      }
      $conversion_depth--;
    };

    // Get the convert function.
    $convert = 'convert' . $type;

    // Normalize the convert_path.
    $convert_path = strtr(strtolower($convert_path), array(
      ':' => '->',
      'jpeg' => 'jpg',
    ));

    // Handle the configured replacements.
    if ($conversion_depth == 1) {
      $ext = preg_replace('@->.*$@', '', $convert_path);
      foreach ($this->replacements as $mode => $replaces) {
        if (!empty($replaces)) {
          $convert_path_replace = "$ext~$mode";
          $engines = $this->getEngines($convert_path_replace, array(
            'replacements' => $replaces,
          ));
          if (!empty($engines)) {
            $engine = &$engines[0];
            if ($type === 'file') {
              $tmp_d = $engine->getTempFile($ext);
              $cleanup_files[] = $tmp_d;
            }
            else {
              $tmp_d = '';
            }
            foreach ($engines as $engine) {
              try {
                $engine->$convert($source, $tmp_d);
                $this->previous_engines[] = $engine;
                break;
              } catch (\Exception $e) {
              }
            }
            $source = $tmp_d;
          }
          else {
            echo "No replacement engines are available.\n";
          }
        }
      }
    }

    // Select a converter.
    $engines = $this->getEngines($convert_path);

    // Remember the file stats of the source file.
    $file_stat = NULL;
    if ($type === 'file' && is_file($source)) {
      $file_stat = stat($source);
    }

    // Attempt to convert the file.
    $errors = array();
    foreach ($engines as $engine) {
      try {
        $engine->$convert($source, $destination);
        $this->previous_engines[] = $engine;
        $return();

        // Preserve the same owner/mode as the source.
        if ($type === 'file' && is_file($destination) && isset($file_stat)) {
          chown($destination, $file_stat['uid']);
          chgrp($destination, $file_stat['gid']);
          chmod($destination, $file_stat['mode']);
        }
        return $this;
      } catch (\Exception $e) {
        $errors[] = $e->getMessage();
      }
    }

    $return();
    $error = "Unable to convert the file. ";
    $errors = array_unique($errors);
    $error .= join(' ', $errors);
    throw new \ErrorException($error);
  }

  public function convertFile($source, $destination, $convert_path = NULL) {
    if (!isset($convert_path)) {
      $source_ext = strtolower(trim(pathinfo($source, PATHINFO_EXTENSION)));
      if ($source_ext === '' && is_file($source)) {
        $source_ext = FileInfo::detectNormalExtension($source);
      }
      $destination_ext = pathinfo($destination, PATHINFO_EXTENSION);
      $convert_path = $source_ext . '->' . $destination_ext;
    }
    $this->convert('file', $convert_path, $source, $destination);
    return $this;
  }

  public function convertString($source, &$destination, $convert_path = 'null->null') {
    $this->convert('string', $convert_path, $source, $destination);
    return $this;
  }

  public function getCommandAlias($command) {
    if (isset($this->settings['alias'][$command])) {
      return $this->settings['alias'][$command];
    }
    return NULL;
  }

  public function getConvertedString($source, $convert_path = 'null->null') {
    $destination = '';
    $this->convertString($source, $destination, $convert_path);
    return $destination;
  }

  public function getSetting($key) {
    return $this->settings[$key];
  }

  public function getSettings() {
    return $this->settings;
  }

  public function getEngineConfigurations() {
    return $this->configurations;
  }

  public function getEngine($convert_path, $configuration) {
    if (!isset($convert_path)) {
      return new Engine\Invalid($this, $convert_path, $this->settings, $configuration);
    }
    $engine_id = $configuration['#engine'];
    if ($engine_id{0} !== '\\') {
      $class = '\FileConverter\Engine\\' . $engine_id;
      if (class_exists($class)) {
        return new $class($this, $convert_path, $this->settings, $configuration);
      }
    }
    if (class_exists($engine_id)) {
      return new $engine_id($this, $convert_path, $this->settings, $configuration);
    }
    return new Engine\Invalid($this, $convert_path, $this->settings, $configuration);
  }

  public function getEngines($convert_path, $configuration_overrides = NULL, $confirm_available = TRUE) {
    // Normalize certain file extensions.
    $normalize = array(
      'htm' => 'html',
      'jpeg' => 'jpg',
    );
    $tmp = explode('->', $convert_path);
    foreach ($tmp as &$k) {
      if (isset($normalize[$k])) {
        $k = $normalize[$k];
      }
    }
    $convert_path = implode('->', $tmp);
    unset($k);

    // Select a converter.
    $force_id = NULL;
    $engines = array();
    foreach ($this->configurations as $conf) {
      // See whether a configuration matches.
      $converters_all = $conf->getAllConverters();
      $converters_potential = array();
      foreach ($converters_all as $convert_path_match => $converters_match) {
        $convert_path_match = preg_replace('@^(.*)~optimize$@', '\1->\1', $convert_path_match);
        if ($convert_path_match === $convert_path) {
          $converters_potential = array_merge($converters_potential, $converters_match);
          continue;
        }
        if (strpos($convert_path_match, '(') === FALSE) {
          continue;
        }
        $regex = '@^' . str_replace('@', '\\@', $convert_path_match) . '$@s';
        if (preg_match($regex, $convert_path)) {
          $converters_potential = array_merge($converters_potential, $converters_match);
        }
      }

      // Continue to the next configuration when there are no matches.
      if (empty($converters_potential)) {
        continue;
      }

      // Check all configurations to load the engines.
      foreach ($converters_potential as $conf_id => $configuration) {
        if (is_string($configuration)) {
          if (!isset($force_id)) {
            $engines = array();
            $force_id = $configuration;
          }
          continue;
        }
        if (isset($force_id)) {
          if ($conf_id !== $force_id) {
            continue;
          }
        }
        if (isset($configuration_overrides)
          && is_array($configuration_overrides)) {
          $configuration = array_replace($configuration, $configuration_overrides);
        }
        $engine = $this->getEngine($convert_path, $configuration);
        if (!$confirm_available || $engine->isAvailable()) {
          $engines[] = $engine;
        }
        else {
          $this->missing_engines[$conf_id] = $engine;
        }

        // Stop if the engine is marked as #final
        // This is similar to setConverter('a->b', 'force_id'),
        //   except that it might not be the first engine tried.
        if (isset($configuration['#final'])) {
          break (2);
        }
      }
    }

    if (isset($force_id) && empty($engines)) {
      if (isset($this->missing_engines[$force_id])) {
        $this->missing_engines = array(
          $force_id => $this->missing_engines[$force_id]
        );
      }
      else {
        $this->missing_engines = array();
      }
    }

    // Provide installation instructions when no engines are available.
    if (empty($engines)) {
      echo "No conversion engines are available for $convert_path.\n";
      echo "Installation instructions:\n";
      foreach ($this->missing_engines as $engine_id => $engine) {
        echo $engine->getHelp('installation') . "\n";
      }
      return array();
    }

    return $engines;
  }

  public function getMissingEngines() {
    return $this->missing_engines;
  }

  public function getPreviousEngines() {
    return $this->previous_engines;
  }

  public function getVersionInfo() {
    $info = array();
    foreach ($this->previous_engines as $engine) {
      $info = array_merge($info, $engine->getVersionInfo());
    }
    ksort($info);
    return $info;
  }

  public function optimizeFile($source = NULL, $destination = NULL, $ext = NULL) {
    if (!isset($source) || !is_file($source)) {
      throw new \InvalidArgumentException("Invalid file path to optimize.");
    }
    if (!isset($ext)) {
      $ext = pathinfo($source, PATHINFO_EXTENSION);
    }
    if (!isset($destination)) {
      $destination = $source;
    }
    return $this->convert('file', "$ext->$ext", $source, $destination);
  }

  public function &setCommandAlias($alias, $path) {
    $this->settings['alias'][$alias] = $path;
  }

  public function &setConverter($convert_path = 'null->null', $configuration = 'null:default') {
    $conf = new ConfigurationOverride($this->settings);
    $conf->setConverter($convert_path, $configuration);
    array_unshift($this->configurations, $conf);
    return $this;
  }

  public function &setReplacements($hash, $mode = 'string') {
    if (!isset($this->replacements[$mode])) {
      throw new \UnexpectedValueException("Invalid replacement mode.");
    }
    $this->replacements[$mode] = array_merge($this->replacements[$mode], (array) $hash);
    return $this;
  }

  public function &setSetting($key, $value) {
    $this->settings[$key] = $value;
    return $this;
  }

  public function &setSettings($settings) {
    $this->settings = array_merge($this->settings, (array) $settings);
    return $this;
  }

}