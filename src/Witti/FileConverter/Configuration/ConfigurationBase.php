<?php
namespace Witti\FileConverter\Configuration;

abstract class ConfigurationBase {
  abstract public function getAllConverters();

  protected $converters = array();
  protected $settings = array();

  public function __construct(&$settings) {
    $this->settings =& $settings;
  }

}