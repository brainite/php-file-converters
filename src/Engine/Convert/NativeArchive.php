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
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Mime;
use Zend\Mime\Part as MimePart;

class NativeArchive extends EngineBase {
  /**
   * @todo use Message::fromString($raw) to convert from eml to other formats
   */
  public function convertFile($source, $destination) {
    if ($this->conversion[0] === 'html' && $this->conversion[1] === 'eml') {
      $parts = array(
        'html' => NULL,
      );

      // Load the HTML body.
      $html = file_get_contents($source);
      $html = preg_replace_callback('@(?<a>\s+src=")(?<src>[^"]+)(?<b>")@si', function ($matches) use (&$parts) {
        // Mangle the src attribute.
        $src = $matches['src'];
        if (strpos($src, '://') === FALSE && !preg_match('@^/|\.\.@s', $src)) {
          if (is_file($src)) {
            $cid = 'imageid' . sizeof($parts);
            $ext = strtolower(pathinfo($src, PATHINFO_EXTENSION));
            if ($ext === 'jpg') {
              $ext = 'jpeg';
            }
            $parts[$cid] = new MimePart(fopen($src, 'r'));
            $parts[$cid]->type = "image/$ext; name=$cid.$ext";
            $parts[$cid]->encoding = Mime::ENCODING_BASE64;
            $parts[$cid]->id = $cid;
            $src = "cid:$cid";
          }
        }

        // Rebuild the src attribute.
        $ret = $matches['a'] . $src . $matches['b'];
        return $ret;
      }, $html);

      // Build the message.
      $parts['html'] = new MimePart($html);
      $parts['html']->type = Mime::TYPE_HTML;
      $parts['html']->charset = 'utf-8';
      $parts['html']->encoding = Mime::ENCODING_QUOTEDPRINTABLE;
      $body = new MimeMessage();
      $body->setParts(array_values($parts));

      // Build the full output.
      $message = new Message();
      $message->setBody($body);
      $output = $message->toString();
      file_put_contents($destination, $output);
      return $this;
    }

    throw new \InvalidArgumentException("Unsupported conversion type requested");
  }

  protected function getHelpInstallation($os, $os_version) {
    $help = array(
      'title' => 'Native Archives',
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
    return (class_exists('\Zend\Mail\Message'));
  }

}