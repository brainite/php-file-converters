<?php
/*
 * This file is part of the Witti FileConverter package.
 *
 * (c) Greg Payne
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Witti\FileConverter\Configuration;

class ConfigurationOverride extends ConfigurationBase {
  public function getAllConverters() {
    return $this->converters;
  }

  public function setConverter($convert_path = 'null -> null', $configuration = 'null:default') {
    if (is_string($configuration)) {
      $this->converters[$convert_path] = array(
        $configuration
      );
    }
    elseif (isset($configuration['#engine'])) {
      $this->converters[$convert_path] = array(
        $configuration,
      );
    }
    else {
      $this->converters[$convert_path] = $configuration;
    }
  }

}