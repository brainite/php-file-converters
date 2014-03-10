<?php
namespace FileConverter\Engine\Optimize;

use FileConverter\Engine\EngineBase;
use PhpQuery\PhpQuery;

/**
 * This engine relies heavily on PhpQuery.
 * @link https://code.google.com/p/phpquery/wiki/Basics
 * @link https://github.com/electrolinux/phpquery
 */
abstract class FcHtmlBase extends EngineBase {
  protected function doRemoveNamespaces(&$pq) {
    $xsl = <<< ____EOF
      <xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" exclude-result-prefixes="xsl">
          <xsl:template match="*">
              <xsl:element name="{local-name()}">
                  <xsl:apply-templates select="@* | node()"/>
              </xsl:element>
          </xsl:template>
          <xsl:template match="@* | text()">
              <xsl:copy/>
          </xsl:template>
      </xsl:stylesheet>
____EOF;

    $xsl = \DOMDocument::loadXml($xsl);
    $proc = new \XSLTProcessor;
    $proc->importStyleSheet($xsl);
    $pq->document = $proc->transformToDoc($pq->document);

    for ($i = $pq->document->documentElement->attributes->length; $i >= 0; --$i) {
      $attr = $pq->document->documentElement->attributes->item($i);
      if (substr($attr->name, 0, 6) === 'xmlns:') {
        $pq->document->documentElement->removeAttributeNode($attr);
      }
    }
    return $this;
  }

  protected function doRemoveNamespacedNodes(&$pq) {
    $xsl = <<< ____EOF
      <xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" exclude-result-prefixes="xsl">
          <xsl:template match="*[local-name()=name()]">
              <xsl:element name="{local-name()}">
                  <xsl:apply-templates select="@* | node()"/>
              </xsl:element>
          </xsl:template>
          <xsl:template match="@* | text()">
              <xsl:copy/>
          </xsl:template>
      </xsl:stylesheet>
____EOF;
    $xsl = \DOMDocument::loadXml($xsl);
    $proc = new \XSLTProcessor;
    $proc->importStyleSheet($xsl);
    $pq->document = $proc->transformToDoc($pq->document);

    for ($i = $pq->document->documentElement->attributes->length; $i >= 0; --$i) {
      $attr = $pq->document->documentElement->attributes->item($i);
      if (substr($attr->name, 0, 6) === 'xmlns:') {
        $pq->document->documentElement->removeAttributeNode($attr);
      }
    }
    $pq = PhpQuery::newDocumentHTML($pq->document->saveHTML());
    return $this;
  }

  protected function unwrap(&$node) {
    var_dump("removing", get_class($node));
    foreach ($node->childNodes AS $child) {
      $clone = $child->cloneNode(true);
      if (is_object($clone)) {
        $node->parentNode->insertBefore($clone, $node);
      }
    }

    $node->parentNode->removeChild($node);
  }

}