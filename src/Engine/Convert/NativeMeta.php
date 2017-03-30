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
use Splash\Splash;
use QuipXml\Quip;

class NativeMeta extends EngineBase {
  /**
   * @todo use Message::fromString($raw) to convert from eml to other formats
   */
  public function convertFile($source, $destination) {
    $meta = array();

    // Extract meta data based on the file type.
    switch ($this->conversion[0]) {
      case 'pptx':
      // Open the pptx file
        $pptx = new \ZipArchive;
        if (TRUE !== $pptx->open($source)) {
          throw new \ErrorException("Unable to open the PPTX file");
        }

        // Build the list of files.
        $files = array();
        for ($i = 0; $i < $pptx->numFiles; $i++) {
          $files[] = $pptx->getNameIndex($i);
        }
        $files = Splash::fromArray($files);

        // Build the slides.
        $meta['items'] = array();
        foreach ($files->regex("@ppt/slides/slide\d+.xml$@") as $file) {
          $slide = array();
          $number = preg_replace('@^ppt/slides/slide(\d+)\.xml$@s', '\1', $file);
          $slide['number'] = $number;

          // Detect the title.
          // ppt/slides/slide1.xml
          $xml_slide = Quip::load($pptx->getFromName($file));
          $title = $xml_slide->xpath("//p:sp//p:ph[@type='title' or @type='ctrTitle']")->xpath('../../..')->html();
          // http://www.datypic.com/sc/ooxml/e-a_br-1.html
          $title = preg_replace('@<a:br[^>]*>@s', "\n", $title);
          $title = trim(strip_tags($title));
          $slide['title'] = $title;

          // Detect any notes.
          // ppt/slides/_rels/slide1.xml.rels
          // The 'Relationship' tag name does not work in this xpath for unknown reasons.
          $xml_rels = Quip::load($pptx->getFromName("ppt/slides/_rels/slide$number.xml.rels"));
          $note_id = $xml_rels->xpath("//*[@Type='http://schemas.openxmlformats.org/officeDocument/2006/relationships/notesSlide']")->eq(0)['Target'];

          // Load the notes from the connected XML.
          // ../notesSlides/notesSlide1.xml
          // becomes ppt/notesSlides/notesSlide1.xml
          if (substr($note_id, 0, 3) === '../') {
            $note_id = preg_replace('@^\.\./@', 'ppt/', $note_id);
            $xml_note = Quip::load($pptx->getFromName($note_id));
            $note = $xml_note->html();
            // The a:p tag appears preferred within notes.
            $note = preg_replace('@<a:br[^>]*>@s', "\n", $note);
            $note = preg_replace('@<a:p(?:\s[^>]*)?>@s', "\n", $note);
            // The slide number appears in notes within a:fld.
            $note = preg_replace('@<a:fld.*?</a:fld>@s', "", $note);
            $note = trim(strip_tags($note));
            $slide['notes'] = $note;
          }

          $meta['items'][$number - 1] = $slide;
        }
        ksort($meta['items']);

        break;

      default:
        throw new \InvalidArgumentException("Unsupported conversion source type requested");
    }

    switch ($this->conversion[1]) {
      case 'json':
        $output = json_encode($meta, JSON_PRETTY_PRINT
          | JSON_PARTIAL_OUTPUT_ON_ERROR);
        file_put_contents($destination, $output);
        return $this;

      default:
        throw new \InvalidArgumentException("Unsupported conversion destination type requested");
    }
  }

  protected function getHelpInstallation($os, $os_version) {
    $help = array(
      'title' => 'Native Meta Data Extractor',
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