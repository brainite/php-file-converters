<?php
/*
 * This file is part of the FileConverter package.
 *
 * (c) Greg Payne
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Witti\FileConverter\Engine\Optimize;
use Witti\FileConverter\Engine\EngineBase;
use Witti\FileConverter\Util\Shell;
/**
 * @todo Add support for field replacement by creating xfdf XML that pdftk can use.
 * @link http://partners.adobe.com/public/developer/en/xml/XFDF_Spec_3.0.pdf
 *
 */
class Pdftk extends EngineBase {
  public function convertFile($source, $destination) {
    // XMP Metadata was added in version 1.4 of the PDF format.
    // Thus, this setting can be ignored on older formats.
    // If left enabled, then pdftk will upgrade the version to 1.4.
    $remove_meta = isset($this->configuration['remove-metadata'])
      && $this->configuration['remove-metadata'];
    if ($remove_meta) {
      // Only remove metadata if this is a 1.4 PDF and there is some metadata
      $pdfVersion = file_get_contents($source, FALSE, NULL, 0, 1024);
      if (preg_match('@^%PDF-(\d+\.\d+).*/(?:Metadata|M) @s', $pdfVersion, $arr)) {
        $pdfVersion = (float) $arr[1];
        if ($pdfVersion < 1.4) {
          $remove_meta = FALSE;
        }
      }
      else {
        $remove_meta = FALSE;
      }
    }

    // One convert function is required to avoid recursion.
    $empty = $this->getTempFile('txt');
    $cleaned = $destination;
    if ($source === $destination || $remove_meta) {
      $cleaned = $this->getTempFile('pdf');
    }

    // pdftk source.pdf dump_data | sed 's/Value: .*/Value:/' > empty.txt
    $this->shell(array(
      $this->cmd,
      $source,
      'dump_data',
      Shell::arg('|', Shell::SHELL_SAFE),
      Shell::arg("sed 's/Value: .*/Value:/' > ", Shell::SHELL_SAFE),
      $empty,
    ));

    // pdftk source.pdf update_info empty.txt output destination.pdf flatten compress
    $cmd = array(
      $this->cmd,
      $source,
      'update_info',
      $empty,
      'output',
      '-',
      'flatten',
      ($remove_meta ? 'uncompress' : 'compress'),
    );
    if (isset($this->configuration['remove-id'])
      && $this->configuration['remove-id']) {
      // /ID [<e8c87bb7a19df73c042da3b2a01dc7ff> <e8c87bb7a19df73c042da3b2a01dc7ff>]
      $cmd[] = Shell::arg(" | grep -va '^\\/ID \\[' ", Shell::SHELL_SAFE);
    }
    $cmd[] = Shell::arg('>', Shell::SHELL_SAFE);
    $cmd[] = $cleaned;
    $this->shell($cmd);

    // Clean up and finalize files.
    if ($remove_meta) {
      $temp = $this->getTempFile('pdf');
      $fp = fopen($cleaned, 'r');
      $fout = fopen($temp, 'w');
      $context = 'none';
      while (!feof($fp)) {
        // Go to the next object.
        $buffer = fgets($fp);
        if (!preg_match('@^\d+ \d+ obj@', $buffer)) {
          fputs($fout, $buffer);
          continue;
        }

        // Load the object def
        $def = array();
        while (!feof($fp)) {
          $line = fgets($fp);
          if (preg_match('@^/(.*?) (.*)$@', $line, $arr)) {
            if ($arr[1] === 'Metadata') {
              continue;
            }
            // Remove the date modified.
            // This should specifically affect type = Annot (i.e., annotations)
            if ($arr[1] === 'M') {
              continue;
            }
            $def[$arr[1]] = trim($arr[2]);
          }
          $buffer .= $line;
          if (substr($line, 0, 2) === '>>') {
            break;
          }
        }

        if ($def['Type'] === '/Metadata') {
          // Remove it.
          if (isset($def['Length'])) {
            $len = (int) $def['Length'];
            while ($len > 0 && !feof($fp)) {
              $line = fgets($fp);
              $len -= strlen($line);
            }
          }
          while (substr($line, 0, 6) !== 'endobj' && !feof($fp)) {
            $line = fgets($fp);
          }
          fputs($fout, $line);
        }
        else {
          // Keep it.
          fputs($fout, $buffer);
          if (isset($def['Length'])) {
            $len = (int) $def['Length'];
            while ($len > 0 && !feof($fp)) {
              $line = fgets($fp);
              $len -= strlen($line);
              fputs($fout, $line);
            }
          }
          while (substr($line, 0, 6) !== 'endobj' && !feof($fp)) {
            $line = fgets($fp);
            fputs($fout, $line);
          }
        }
      }
      fclose($fp);
      fclose($fout);
      $this->shell(array(
        $this->cmd,
        $temp,
        'update_info',
        $empty,
        'output',
        $destination,
        'flatten',
        'compress',
      ));
      unlink($temp);
      if ($cleaned !== $destination) {
        unlink($cleaned);
      }
    }
    elseif ($cleaned !== $destination) {
      rename($cleaned, $destination);
    }
    unlink($empty);

    return $this;
  }

  protected function getHelpInstallation($os, $os_version) {
    $help = array(
      'title' => 'PdfTk',
    );
    switch ($os) {
      case 'Ubuntu':
        $help['os'] = 'confirmed on Ubuntu 12.04';
        $help['apt-get'] = 'pdftk';
        return $help;
    }

    return parent::getHelpInstallation($os, $os_version);
  }

  public function isAvailable() {
    $this->cmd = $this->shellWhich('pdftk');
    return isset($this->cmd);
  }
}