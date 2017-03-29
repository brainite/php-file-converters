<?php
/*
 * This file is part of the FileConverter package.
 *
 * (c) Greg Payne
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FileConverter\Engine\Helper;

use FileConverter\Engine\EngineBase;
use FileConverter\Util\Shell;
class Archive {
  /**
   * @var EngineBase
   */
  protected $engine = NULL;
  /**
   * @var string
   */
  protected $tempDirectory = NULL;

  public function __construct(EngineBase &$engine) {
    $this->engine =& $engine;
  }

  public function __destruct() {
    if (isset($this->tempDirectory)) {
      $this->engine->shell(array(
        'rm',
        Shell::arg('rf', Shell::SHELL_ARG_BOOL_SGL, TRUE),
        Shell::arg($this->tempDirectory, Shell::SHELL_ARG_BASIC),
      ));
    }
  }

  public function getTempDirectory() {
    if (!isset($this->tempDirectory)) {
      $this->tempDirectory = $this->engine->getTempFile('dir');
      @unlink($this->tempDirectory);
      mkdir($this->tempDirectory);
    }
    return $this->tempDirectory;
  }

  public function save($destination) {
    switch ($ext = $this->engine->getConversion('destination', 1)) {
      case 'zip':
        $temp = $this->engine->getTempFile('zip');
        @unlink($temp);
        $cmd = array(
          'cd',
          Shell::arg($this->getTempDirectory(), Shell::SHELL_ARG_BASIC),
          Shell::arg('; ', Shell::SHELL_SAFE),
          $this->engine->shellWhich('zip'),
          Shell::arg('r', Shell::SHELL_ARG_BOOL_SGL, TRUE),
          Shell::arg($temp, Shell::SHELL_ARG_BASIC),
          Shell::arg('.', Shell::SHELL_SAFE),
        );
        $this->engine->shell($cmd);
        rename($temp, $destination);
        break;

      case 'directory':
        if (!is_dir($destination)) {
          mkdir($destination);
          if (!is_dir($destination)) {
            throw new \ErrorException("Unable to create the destination directory.");
          }
        }
        $cmd = array(
          $this->engine->shellWhich('rsync'),
          Shell::arg('a', Shell::SHELL_ARG_BOOL_SGL, TRUE),
          Shell::arg(rtrim($this->getTempDirectory(), '/') . '/', Shell::SHELL_ARG_BASIC),
          Shell::arg(rtrim($destination, '/') . '/', Shell::SHELL_ARG_BASIC),
        );
        $this->engine->shell($cmd);
        break;

      default:
        throw new \InvalidArgumentException("Invalid archive format spec: $ext");
    }
  }

}
