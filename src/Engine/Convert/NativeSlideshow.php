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
use FileConverter\Engine\Helper\Archive;
use GuzzleHttp\json_decode;

class NativeSlideshow extends EngineBase {
  /**
   * @todo use Message::fromString($raw) to convert from eml to other formats
   */
  public function convertFile($source, $destination) {
    // Add default configurations
    $this->configuration = array_replace(array(
      'mode' => 'default',
    ), (array) $this->configuration);

    // Create a folder containing all of the images
    $imageArchive = new Archive($this);
    $imagePath = $imageArchive->getTempDirectory();
    $this->converter->convertFile($source, $imagePath, $this->conversion[0]
      . '->directory/jpg');

    // Build the slideshow configuration
    $slideshow = array(
      'title' => 'Slideshow',
      'items' => array(),
    );
    foreach ($this->configuration as $k => $v) {
      if ($k{0} !== '#') {
        $slideshow[$k] = $v;
      }
    }
    try {
      // Get the configuration for the slides.
      $tmpJson = $this->getTempFile('.json');
      $this->converter->convertFile($source, $tmpJson, $this->conversion[0]
        . '->json');
      $slideshow = array_replace($slideshow, (array) json_decode(file_get_contents($tmpJson), TRUE));

      // Apply the slideshow mode.
      foreach ($slideshow['items'] as $i => &$slide) {
        if (!isset($slide['notes'])) {
          if ($slideshow['mode'] === 'schedule') {
            unset($slideshow['items'][$i]);
          }
        }
        else {
          // Extract settings from the slide notes.
          $keys = array(
            '@(?<k>SLIDESHOW\.[^:]+):(?<v>[^\n]+)(?:\n|$)@s',
            '@(?<k>BEGIN):(?<v>[^\n]+)(?:\n|$)@s',
            '@(?<k>TITLE):(?<v>[^\n]+)(?:\n|$)@s',
            '@(?<k>DESCRIPTION):(?<v>[^\n]+)(?:\n|$)@s',
          );
          foreach ($keys as $match) {
            if (preg_match($match, $slide['notes'], $arr)) {
              if (strpos($arr['k'], 'SLIDESHOW.') === 0) {
                $slideshow[strtolower(substr($arr['k'], 10))] = $arr['v'];
              }
              else {
                $slide[strtolower($arr['k'])] = $arr['v'];
              }
            }
          }
          unset($slide['notes']);
        }
      }
      unset($slide);
      $slideshow['items'] = array_values($slideshow['items']);
    } catch (\Exception $e) {
      // Build the default meta data based on the images.
      $slideshow['mode'] = 'default';
      $i = 1;
      while (is_file("$imagePath/page$i.jpg")) {
        $slideshow['items'][] = array(
          'number' => $i,
          'title' => "Slide $i",
        );
        ++$i;
      }
    }

    // Build the actual slideshow archive.
    $archive = new Archive($this);
    $tmp = $archive->getTempDirectory();
    file_put_contents("$tmp/meta.json", json_encode($slideshow));

    // Copy the images as appropriate.
    foreach ($slideshow['items'] as $slide) {
      $from = "$imagePath/page$slide[number].jpg";
      $to = "$tmp/img/slide$slide[number].jpg";
      $this->isTempWritable($to);
      copy($from, $to);
    }

    // Copy the slideshow resources.
    $to = "$tmp/js/slideshow.js";
    $this->isTempWritable($to);
    copy(__DIR__ . '/Resources/Slideshow/slideshow.js', $to);
    $to = "$tmp/css/slideshow.css";
    $this->isTempWritable($to);
    copy(__DIR__ . '/Resources/Slideshow/slideshow.css', $to);

    // The index file involves template variables.
    // Provide a raw
    $to = "$tmp/index.htm";
    $this->isTempWritable($to);
    $content = file_get_contents(__DIR__ . '/Resources/Slideshow/index.htm');
    $content = strtr($content, array(
      '{{ slideshow.title|escape }}' => htmlspecialchars($slideshow['title']),
      '{{ slideshow|json_encode() }}' => json_encode($slideshow),
    ));
    file_put_contents($to, $content);

    // Save the slideshow.
    $archive->save($destination);
  }

  protected function getHelpInstallation($os, $os_version) {
    $help = array(
      'title' => 'Native Slideshow Extractor',
    );
    switch ($os) {
      case 'Ubuntu':
        $help['os'] = 'confirmed on Ubuntu 16.04';
        $help['notes'] = array(
          'composer update',
        );
        return $help;
    }

    return parent::getHelpInstallation($os, $os_version);
  }

  public function isAvailable() {
    return TRUE;
  }

}