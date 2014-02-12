<?php
/*
 * This file is part of the Witti FileConverter package.
 *
 * (c) Greg Payne
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Witti\FileConverter\Util;

class Shell {
  const SHELL_SAFE = 1;
  const SHELL_OPTIONS = 2;
  const SHELL_ARG_BASIC = 3;
  const SHELL_ARG_BASIC_DBL = 4;
  const SHELL_ARG_BASIC_DBL_NOEQUAL = 8;
  const SHELL_ARG_BOOL_DBL = 5;
  const SHELL_ARG_BOOL_SGL = 7;
  const SHELL_ARG_BASIC_SGL = 6;
  const SHELL_STDERR = 9;

  static public function arg($arg, $mode, $value = NULL) {
    return new Shell($arg, $mode, $value);
  }

  static public function argDouble($arg, $value = TRUE, $mode = NULL) {
    if (isset($mode)) {
      return new Shell($arg, $mode, $value);
    }
    elseif (is_bool($value)) {
      return new Shell($arg, Shell::SHELL_ARG_BOOL_DBL, $value);
    }
    else {
      return new Shell($arg, Shell::SHELL_ARG_BASIC_DBL, $value);
    }
  }

  static public function argOptions($options, $configuration, $group_id = NULL) {
    $relevant = array();
    foreach ($options as $option) {
      if (isset($configuration[$option['name']])) {
        $option['value'] = $configuration[$option['name']];
      }
      elseif (isset($option['default'])) {
        $option['value'] = $option['default'];
      }
      else {
        $option['value'] = NULL;
      }
      if (!isset($group_id) || !isset($option['group'])
        || $group_id === $option['group']) {
        $relevant[] = $option;
      }
    }
    return new Shell($group_id, Shell::SHELL_OPTIONS, $relevant);
  }

  private $argument = NULL;
  private $mode = NULL;
  private $value = NULL;
  public function __construct($arg, $mode, $value = NULL) {
    $this->argument = $arg;
    $this->mode = $mode;
    $this->value = $value;
  }

  public function getMode() {
    return $this->mode;
  }

  public function getValue() {
    return $this->value;
  }

  public function render() {
    switch ($this->mode) {
      case Shell::SHELL_SAFE:
        return $this->argument;

      case Shell::SHELL_OPTIONS:
        $output = '';
        foreach ($this->value as $opt) {
          $tmp = Shell::arg($opt['name'], $opt['mode'] ? $opt['mode']
            : Shell::SHELL_ARG_BASIC, $opt['value'])->render();
          if ($tmp !== '') {
            $output .= ' ' . $tmp;
          }
        }
        return $output;

      case Shell::SHELL_ARG_BOOL_DBL:
        if ($this->value) {
          return escapeshellarg('--' . $this->argument);
        }
        return '';

      case Shell::SHELL_ARG_BOOL_SGL:
        if ($this->value) {
          return escapeshellarg('-' . $this->argument);
        }
        return '';

      case Shell::SHELL_ARG_BASIC_DBL:
        if (isset($this->value)) {
          return escapeshellarg('--' . $this->argument . '=' . $this->value);
        }
        return '';

      case Shell::SHELL_ARG_BASIC_DBL_NOEQUAL:
        if (isset($this->value)) {
          return escapeshellarg('--' . $this->argument) . ' ' . escapeshellarg($this->value);
        }
        return '';

      case Shell::SHELL_ARG_BASIC_SGL:
        if (isset($this->value)) {
          return escapeshellarg('-' . $this->argument) . ' '
            . escapeshellarg($this->value);
        }
        return '';

      case Shell::SHELL_STDERR:
        return '2>' . escapeshellarg($this->value);

      case Shell::SHELL_ARG_BASIC:
      default:
        return escapeshellarg($this->argument);
    }
  }
}