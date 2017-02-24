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
class LibreOffice extends EngineBase {
  protected $cmd_source_safe = FALSE;

  public function getConvertFileShell($source, &$destination) {
    // Attempt to initialize and override registrymodifications.xcu
    if (!is_dir($this->settings['temp_dir']) . '/libreoffice') {
      // Run a basic conversion to attempt to init the system.
      $tmp = $this->getTempFile('txt');
      file_put_contents($tmp, "Init LibreOffice configs");
      $this->shell(array(
        Shell::arg('export HOME=' . escapeshellarg($this->settings['temp_dir']) . ';', Shell::SHELL_SAFE),
        $this->cmd,
        '--headless',
        '--convert-to',
        $this->conversion[1],
        '--outdir',
        $this->settings['temp_dir'],
        $tmp
      ));
      @unlink($tmp);
      @unlink(str_replace('.txt', "." . $this->conversion[1], $tmp));

      // Update the registry, if it was properly initialized.
      $reg = $this->settings['temp_dir'] . "/libreoffice/4/user/registrymodifications.xcu";
      if (is_file($reg)) {
        copy(__DIR__ . "/Resources/LibreOffice-registrymodifications.xcu", $reg);
      }
    }

    // Build the conversion command.
    $destination = str_replace('.' . $this->conversion[0], '.'
      . $this->conversion[1], $source);
    return array(
      Shell::arg('export HOME=' . escapeshellarg($this->settings['temp_dir']) . ';', Shell::SHELL_SAFE),
      $this->cmd,
      '--headless',
      '--convert-to',
      $this->conversion[1],
      '--outdir',
      $this->settings['temp_dir'],
      $source
    );
  }

  protected function getHelpInstallation($os, $os_version) {
    $help = array(
      'title' => 'LibreOffice',
    );
    switch ($os) {
      case 'Ubuntu':
        $help['os'] = 'confirmed on Ubuntu 12.04';
        $help['apt-get'] = 'libreoffice';
        $help['notes'] = array(
          "/usr/bin/libreoffice is symlink to /usr/lib/libreoffice/program/soffice",
          "/usr/bin/soffice (competes with other apps) is symlink to /usr/lib/libreoffice/program/soffice",
        );
        return $help;
    }

    return parent::getHelpInstallation($os, $os_version);
  }

  public function getInfo() {
    $info = array(
      'title' => 'LibreOffice',
      'url' => 'http://www.libreoffice.org/',
      'profile' => array(
        'Ubuntu 12.04' => array(
          'os' => 'confirmed on Ubuntu 12.04',
          'apt-get' => 'libreoffice',
          'notes' => array(
            "/usr/bin/libreoffice is symlink to /usr/lib/libreoffice/program/soffice",
            "/usr/bin/soffice (competes with other apps) is symlink to /usr/lib/libreoffice/program/soffice",
          ),
        ),
      ),
      'version' => array(
        'LibreOffice' => NULL,
      ),
    );

    if ($this->isAvailable()) {
      $info['version'] = $this->getVersionInfo();
    }

    return $info;
  }

  public function getVersionInfo() {
    $info = array(
      'LibreOffice' => $this->shell($this->cmd . " --version")
    );
    $info["LibreOffice"] = preg_replace('@LibreOffice *@si', '', $info['LibreOffice']);
    return $info;
  }

  public function isAvailable() {
    $this->cmd = $this->shellWhich('libreoffice');
    return isset($this->cmd);
  }

}