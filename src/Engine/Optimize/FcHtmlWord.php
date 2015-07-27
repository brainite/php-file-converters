<?php
namespace FileConverter\Engine\Optimize;
use QuipXml\Quip;

/**
 * This engine relies heavily on QuipXml.
 * @version 0.2
 */
class FcHtmlWord extends FcHtmlBase {
  public function convertString($source, &$destination) {
    $source = str_replace("\r\n", "\n", $source);
    $source = str_replace("\r", "\n", $source);
    $quip = Quip::load($source, 0, FALSE, '', FALSE, Quip::LOAD_NS_UNWRAP);
    try {

      // Unwrap spelling/grammar errors.
      $found = TRUE;
      while ($found) {
        $found = FALSE;
        $quip->xpath("//span[matches(@class, '(SpellE|GramE)'")->unwrap();
      }

      // Remove references to external resource files.
      $quip->xpath("//link[@rel = 'themeData']")->remove();
      $quip->xpath("//link[@rel = 'colorSchemeMapping']")->remove();
      $quip->xpath("//link[@rel = 'File-List']")->remove();
//       $quip->xpath("//meta[@http-equiv = 'Content-Type']")->not(':first')->remove();
      $quip->xpath("//meta[@name]")->remove();

      // Iterate through <style> tags and remove mso-* properties
      foreach ($quip->xpath('//style') as $style_node) {
        $style = $style_node->html();
        $prev = '';
        while ($prev !== $style) {
          $prev = $style;
          // Remove mso-
          $style = preg_replace('@([\s;{])mso-.*?;@', '\1', $style);
          // Remove empty blocks
          $style = preg_replace('@\{\s+\}@s', '{}', $style);
          $style = preg_replace("@\}[^\*\{\}/]+?\{\}@s", '}', $style);
          // Blank lines
          $style = preg_replace("@\n\s*\n@s", "\n", $style);
        }
        $style_node->html($style);
      }

    } catch (\Exception $e) {
      echo $e->getMessage();
    }
    $destination = $quip->html();

//     echo $destination;
    return $this;
  }

  public function getVersionInfo() {
    $info = array(
      'php' => phpversion(),
      'FcHtmlWord' => '-1',
    );
    $dat = file_get_contents(__FILE__);
    if (preg_match('~@version\s+([0-9\.]+)~s', $dat, $arr)) {
      $info['FcHtmlWord'] = $arr[1];
    }
    return $info;
  }

  public function isAvailable() {
    if (!class_exists('\QuipXml\Quip')) {
      return FALSE;
    }
    return TRUE;
  }

}