<?php
namespace Witti\FileConverter\Engine\ReplaceString;

use Witti\FileConverter\Engine\EngineBase;
class ReplaceStringNative extends EngineBase {
  public function convertString(&$source, &$destination) {
    $replacements = array();
    if (isset($this->configuration['replacements'])
      && is_array($this->configuration['replacements'])) {
      $replacements = $this->configuration['replacements'];
    }

    switch ($this->conversion[0]) {
      case 'txt~string':
        $destination = strtr($source, $replacements);
        break;

      case 'rtf~string':
        $basic_escape = function (&$v) {
          // @link http://www.biblioscape.com/rtf15_spec.htm
          $v = strtr($v, array(
            '}' => '\}',
            '{' => '\{',
            "\r" => '\\line',
            "\n" => '\\par ',
            "\t" => '\\tab',
            chr(146) => '\\lquote',
            chr(146) => '\\rquote',
            chr(147) => '\\ldblquote',
            chr(148) => '\\rdblquote',
            chr(149) => '\\bullet',
            chr(150) => '\\endash',
            chr(151) => '\\emdash',
          ));
          // Decode three byte unicode characters
          $v = preg_replace("/([\340-\357])([\200-\277])([\200-\277])/e", "'\\u'.((ord('\\1')-224)*4096 + (ord('\\2')-128)*64 + (ord('\\3')-128)).'?'", $v);
          // Decode two byte unicode characters
          $v = preg_replace("/([\300-\337])([\200-\277])/e", "'\\u'.((ord('\\1')-192)*64+(ord('\\2')-128)).'?'", $v);
          // Decode any other non-ASCII characters (\177 hex = 127 dec)
          while (preg_match('/[^\00-\177]/', $v, $arr)) {
            $v = str_replace($arr[0], "\'" . dechex(ord($arr[0])), $v);
          }
        };
        $regex_escape = function (&$v) use ($basic_escape) {
          // For simplicity and faster execution, the unnecessary \r\n characters
          //   are simply stripped out of the source string before using this regex.
          // $invisible = "(?:(?:\\\\[^}{ ]+|\}\{|\\r\\n)+? |\\r\\n)*?";
          // @TODO Bypassing the }{ is not safe since it could match \}{ as well.
          $invisible = "(?:(?:\\\\[^}{ ]+|\}\{)+? )*?";
          $tmp = str_split($v);
          array_shift($tmp);
          $v = '';
          foreach ($tmp as $char) {
            $char = preg_quote($char);
            $basic_escape($char);
            $v .= $char . $invisible;
          }
          $v = "/^$v(.*)$/s";
        };

        // Locate the string placement add placeholders for replacing later.
        // Otherwise, each replacement could replace previous replacements.
        $strtr = array();

        // Chop the file up into smaller chunks for comparisons.
        // Otherwise, the backtrack limit is likely to be hit for larger files.
        $destination = $source;
        $destination = strtr($destination, array(
          "\n" => '',
          "\r" => '',
          "\0" => '',
        ));

        foreach ($replacements as $search => $replace) {
          $first_char = $search{0};
          $basic_escape($first_char);
          $regex_escape($search);

          // The actual new value will be inserted later.
          $basic_escape($replace);
          $unique_string = '[[' . uniqid('FILECONVERTER-REPLACE-', TRUE) . ']]';
          $strtr[$unique_string] = $replace;

          // Split on the first character of the search.
          // Run the regex against a max of the first 1000 characters.
          $parts = explode($first_char, $destination, 2);
          $destination = '';
          while (TRUE) {
            $destination .= array_shift($parts);
            if (empty($parts)) {
              break;
            }
            else {
              $part = $parts[0];
              if (strlen($part) <= 1000) {
                if (preg_match($search, $part, $arr)) {
                  $destination .= $unique_string;
                  $parts = explode($first_char, $arr[1], 2);
                }
                else {
                  $destination .= $first_char;
                  $parts = explode($first_char, $part, 2);
                }
              }
              else {
                if (preg_match($search, substr($part, 0, 2000), $arr)) {
                  $destination .= $unique_string;
                  $parts = explode($first_char, $arr[1] . substr($part, 2000), 2);
                }
                else {
                  $destination .= $first_char;
                  $parts = explode($first_char, $part, 2);
                }
              }
            }
          }
          $destination = strtr($destination, $strtr);

          // If the destination string was destroyed in the process, then reset it to the source.
          if (strlen($destination) == 0) {
            $destination = $source;
            break;
          }
        }

        break;
    }

    return $this;
  }

  public function isAvailable() {
    switch ($this->conversion[0]) {
      case 'txt~string':
      case 'rtf~string':
        return TRUE;

      default:
        return FALSE;
    }
  }
}