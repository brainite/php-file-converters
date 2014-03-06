<?php
/*
 * This file is part of the FileConverter package.
 *
 * (c) Greg Payne
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Witti\FileConverter\Configuration;

abstract class ConfigurationBase {
  abstract public function getAllConverters();

  protected $converters = array();
  protected $settings = array();

  public function __construct(&$settings) {
    $this->settings =& $settings;
  }

}