<?php
/*
 * This file is part of the FileConverter package.
 *
 * (c) Greg Payne
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FileConverter\Engine\Convert;

use FileConverter\Engine\EngineBase;
use FileConverter\Util\Shell;
class Pandoc extends EngineBase {
  protected $cmd_options = array(
    array(
      'name' => 'from',
      'description' => 'Provide a pandoc format constant (e.g., markdown, markdown_github, etc.)',
      'mode' => Shell::SHELL_ARG_BASIC_DBL,
    ),
    array(
      'name' => 'to',
      'description' => 'Provide a pandoc format constant (e.g., markdown, markdown_github, etc.)',
      'mode' => Shell::SHELL_ARG_BASIC_DBL,
    ),
    array(
      'name' => 'data-dir',
      'description' => 'Pandoc user data directory, defaults to $HOME/.pandoc',
      'mode' => Shell::SHELL_ARG_BASIC_DBL,
    ),
    array(
      'name' => 'extract-media',
      'description' => 'Directory to extract embedded images into (Pandoc writes <dir>/media/...)',
      'mode' => Shell::SHELL_ARG_BASIC_DBL,
    ),
    array(
      'name' => 'shift-heading-level-by',
      'description' => 'Integer added to every heading level (e.g., 1 makes a Heading 1 an <h2>)',
      'mode' => Shell::SHELL_ARG_BASIC_DBL,
    ),
  );
  public function getConvertFileShell($source, &$destination) {
    return array(
      $this->cmd['pandoc'],
      Shell::argOptions($this->cmd_options, $this->configuration),
      $source,
      Shell::arg('o', Shell::SHELL_ARG_BASIC_SGL, $destination),
    );
  }

  protected function getHelpInstallation($os, $os_version) {
    $help = array(
      'title' => 'Pandoc',
      'url' => 'http://johnmacfarlane.net/pandoc/',
    );
    switch ($os) {
      case 'Ubuntu':
        $help['os'] = 'confirmed on Ubuntu 12.04';
        $help['apt-get'] = array(
          'pandoc',
          'texlive',
        );
        return $help;
    }

    return parent::getHelpInstallation($os, $os_version);
  }

  public function getVersionInfo() {
    $info = array(
      'pandoc' => 'UNKNOWN',
    );
    $v = $this->shell(array(
      $this->cmd['pandoc'],
      '-v'
    ));
    if (preg_match("@pandoc ([\d\.]+)@s", $v, $arr)) {
      $info['pandoc'] = $arr[1];
    }
    if (isset($this->cmd['latex'])) {
      $latex = basename($this->cmd['latex']);
      $info[$latex] = 'UNKNOWN';
      $v = $this->shell(array(
        $this->cmd['latex'],
        '-v'
      ));
      if (preg_match("@pdfTeX ([\d\.\-]+)@s", $v, $arr)) {
        $info[$latex] = $arr[1];
      }
    }
    return $info;
  }

  /**
   * Pandoc needs LaTeX only to write a PDF.
   * Every other destination (e.g., docx->html) needs the pandoc binary alone.
   */
  public function isAvailable() {
    $this->cmd = array(
      'pandoc' => $this->shellWhich('pandoc'),
      'latex' => NULL,
    );
    if ($this->isLatexRequired()) {
      $this->cmd['latex'] = $this->shellWhich('pdflatex');
      return isset($this->cmd['pandoc']) && isset($this->cmd['latex']);
    }
    return isset($this->cmd['pandoc']);
  }

  /**
   * The engine learns its destination from the convert path it was built for.
   * @return bool
   */
  protected function isLatexRequired() {
    return (bool) preg_match('@^pdf(/|$)@', $this->getConversion('destination'));
  }
}