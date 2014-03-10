<?php
namespace FileConverter\Engine\Optimize;
use PhpQuery\PhpQuery;

/**
 * This engine relies heavily on phpQuery.
 * @link https://code.google.com/p/phpquery/wiki/Basics
 * @link https://github.com/electrolinux/phpquery
 * @version 0.1
 */
class FcHtmlWord extends FcHtmlBase {
  public function convertString($source, &$destination) {
    PhpQuery::useFunction(__NAMESPACE__);
    $source = str_replace("\r\n", "\n", $source);
    $source = str_replace("\r", "\n", $source);
    $pq = PhpQuery::newDocumentHTML($source);
    try {

      $this->doRemoveNamespacedNodes($pq);

      // Unwrap spelling/grammar errors.
      $found = TRUE;
      while ($found) {
        $found = FALSE;
        foreach (pq('span.SpellE,span.GramE') as $el) {
          pq($el)->contentsUnwrap();
        }
      }

      // Remove references to external resource files.
      pq("link[rel=themeData]")->remove();
      pq("link[rel=colorSchemeMapping]")->remove();
      pq("link[rel=File-List]")->remove();
      pq("meta[http-equiv=Content-Type]")->not(':first')->remove();
      pq("meta[name]")->remove();

      // Iterate through <style> tags and remove mso-* properties
      foreach (pq('style') as $style_node) {
        $style = pq($style_node)->html();
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
        pq($style_node)->html($style);
      }

    } catch (\Exception $e) {
      echo $e->getMessage();
    }
    $destination = $pq->document->saveHTML();

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
    if (!class_exists('\PhpQuery\PhpQuery')) {
      return FALSE;
    }
    return TRUE;
  }

}