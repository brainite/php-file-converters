<?php
namespace FileConverter\Engine\Optimize;

use FileConverter\Engine\EngineBase;
use HtmLawed\HtmLawed as hl;

class HtmLawed extends EngineBase {
  protected $cmd_options = array(
    array(
      'name' => 'abs_url',
      'description' => 'Make URLs absolute or relative',
      'examples' => array(
        '-1' => 'make relative',
        '0' => 'no action',
        '1' => 'make absolute',
      ),
      'default' => 0,
    ),
    array(
      'name' => 'and_mark',
      'description' => 'Mark \'&\' characters in the original input',
      'default' => NULL,
    ),
    array(
      'name' => 'balance',
      'description' => 'Balance tags for well-formedness and proper nesting',
      'examples' => array(
        '0' => 'No',
        '1' => 'Yes',
      ),
      'default' => 1,
    ),
    array(
      'name' => 'base_url',
      'description' => 'Base URL value that needs to be set if abs_url is not 0.',
      'examples' => array(
        'http://example.com/' => '',
      ),
      'default' => NULL,
    ),
    array(
      'name' => 'clean_ms_char',
      'description' => 'Replace discouraged characters introduced by Microsoft Word, etc.',
      'examples' => array(
        '0' => 'no',
        '1' => 'yes',
        '2' => 'yes, but replace special single & double quotes with ordinary ones',
      ),
      'default' => 0,
    ),
    array(
      'name' => 'keep_bad',
      'description' => "Neutralize bad tags by converting '<' and '>' to entities, or remove them",
      'examples' => array(
        '0' => 'remove',
        '1' => 'neutralize both tags and element content',
        '2' => 'remove tags but neutralize element content',
        '3' => 'like 1 but remove if text (pcdata) is invalid in parent element',
        '4' => 'like 2 but remove if text (pcdata) is invalid in parent element',
        '5' => 'like 3 but line-breaks, tabs and spaces are left',
        '6' => 'like 4 but line-breaks, tabs and spaces are left',
      ),
      'default' => 6,
    ),
    array(
      'name' => 'comment',
      'description' => 'Handling of HTML comments',
      'examples' => array(
        '0' => 'don\'t consider comments as markup and proceed as if plain text',
        '1' => 'remove',
        '2' => "allow, but neutralize any '<', '>', and '&' inside by converting to named entities",
        '3' => 'allow',
      ),
      'default' => 3,
    ),
    array(
      'name' => 'elements',
      'description' => 'Allowed HTML elements',
      'examples' => array(
        '* -center -dir -font -isindex -menu -s -strike -u' => '',
        'applet, embed, iframe, object, script' => '',
      ),
      'default' => 'a, abbr, acronym, address, applet, area, b, bdo, big, blockquote, br, button, caption, center, cite, code, col, colgroup, dd, del, dfn, dir, div, dl, dt, em, embed, fieldset, font, form, h1, h2, h3, h4, h5, h6, hr, i, iframe, img, input, ins, isindex, kbd, label, legend, li, map, menu, noscript, object, ol, optgroup, option, p, param, pre, q, rb, rbc, rp, rt, rtc, ruby, s, samp, script, select, small, span, strike, strong, sub, sup, table, tbody, td, textarea, tfoot, th, thead, tr, tt, u, ul, var',
    ),
    array(
      'name' => 'tidy',
      'description' => 'Beautify or compact HTML code',
      'examples' => array(
        '-1' => 'compact',
        '0' => 'no',
        '1' => 'yes',
        '3s2n' => '3 spaces, 2 lines',
        '3t2rn' => '3 tabs, 2 returns/lines',
      ),
      'default' => 1,
    ),
  //     array(
  //       'name' => '',
  //       'description' => '',
  //       'examples' => array(
  //         '' => '',
  //         '' => '',
  //         '' => '',
  //       ),
  //       'default' => NULL,
  //     ),
  );
  /*
   * '*' default
   * '^' different default when htmLawed is used in the Kses-compatible mode (see section:- #2.6),
   * '~' different default when 'valid_xhtml' is set to '1' (see section:- #3.5),
   * '"' different default when 'safe' is set to '1' (see section:- #3.6)
   */

  //   *anti_link_spam*
  //   Anti-link-spam measure; see section:- #3.4.7

  //   '0' - no measure taken  *
  //   `array("regex1", "regex2")` - will ensure a 'rel' attribute with 'nofollow' in its value in case the 'href' attribute value matches the regular expression pattern 'regex1', and/or will remove 'href' if its value matches the regular expression pattern 'regex2'. E.g., 'array("/./", "/://\W*(?!(abc\.com|xyz\.org))/")'; see section:- #3.4.7 for more.

  //   *anti_mail_spam*
  //   Anti-mail-spam measure; see section:- #3.4.7

  //   '0' - no measure taken  *
  //   `word` - '@' in mail address in 'href' attribute value is replaced with specified `word`

  //   *cdata*
  //   Handling of 'CDATA' sections; see section:- #3.3.1

  //   '0' - don't consider 'CDATA' sections as markup and proceed as if plain text  ^"
  //   '1' - remove
  //   '2' - allow, but neutralize any '<', '>', and '&' inside by converting them to named entities
  //   '3' - allow  *

  //   *css_expression*
  //   Allow dynamic CSS expression by not removing the expression from CSS property values in 'style' attributes; see section:- #3.4.8

  //   '0' - remove  *
  //   '1' - allow

  //   *deny_attribute*
  //   Denied HTML attributes; see section:- #3.4

  //   '0' - none  *
  //   `string` - dictated by values in `string`
  //   'on*' (like 'onfocus') attributes not allowed - "

  //   *direct_nest_list*
  //   Allow direct nesting of a list within another without requiring it to be a list item; see section:- #3.3.4

  //   '0' - no  *
  //   '1' - yes

  //   *hexdec_entity*
  //   Allow hexadecimal numeric entities and do not convert to the more widely accepted decimal ones, or convert decimal to hexadecimal ones; see section:- #3.2

  //   '0' - no
  //   '1' - yes  *
  //   '2' - convert decimal to hexadecimal ones

  //   *hook*
  //   Name of an optional hook function to alter the input string, '$config' or '$spec' before htmLawed starts its main work; see section:- #3.7

  //   '0' - no hook function  *
  //   `name` - `name` is name of the hook function ('kses_hook'  ^)

  //   *hook_tag*
  //   Name of an optional hook function to alter tag content finalized by htmLawed; see section:- #3.4.9

  //   '0' - no hook function  *
  //   `name` - `name` is name of the hook function

  //   *lc_std_val*
  //   For XHTML compliance, predefined, standard attribute values, like 'get' for the 'method' attribute of 'form', must be lowercased; see section:- #3.4.5

  //   '0' - no
  //   '1' - yes  *

  //   *make_tag_strict*
  //   Transform/remove these non-strict XHTML elements, even if they are allowed by the admin: 'applet' 'center' 'dir' 'embed' 'font' 'isindex' 'menu' 's' 'strike' 'u'; see section:- #3.3.2

  //   '0' - no  ^
  //   '1' - yes, but leave 'applet', 'embed' and 'isindex' elements that currently can't be transformed  *
  //   '2' - yes, removing 'applet', 'embed' and 'isindex' elements and their contents (nested elements remain)  ~

  //   *named_entity*
  //   Allow non-universal named HTML entities, or convert to numeric ones; see section:- #3.2

  //   '0' - convert
  //   '1' - allow  *

  //   *no_deprecated_attr*
  //   Allow deprecated attributes or transform them; see section:- #3.4.6

  //   '0' - allow  ^
  //   '1' - transform, but 'name' attributes for 'a' and 'map' are retained  *
  //   '2' - transform

  //   *parent*
  //   Name of the parent element, possibly imagined, that will hold the input; see section:- #3.3

  //   *schemes*
  //   Array of attribute-specific, comma-separated, lower-cased list of schemes (protocols) allowed in attributes accepting URLs (or '!' to `deny` any URL); '*' covers all unspecified attributes; see section:- #3.4.3

  //   'href: aim, feed, file, ftp, gopher, http, https, irc, mailto, news, nntp, sftp, ssh, telnet; *:file, http, https'  *
  //   '*: ftp, gopher, http, https, mailto, news, nntp, telnet'  ^
  //   'href: aim, feed, file, ftp, gopher, http, https, irc, mailto, news, nntp, sftp, ssh, telnet; style: !; *:file, http, https'  "

  //   *style_pass*
  //   Do not look at 'style' attribute values, letting them through without any alteration

  //   '0' - no *
  //   '1' - htmLawed will let through any 'style' value; see section:- #3.4.8

  //   *unique_ids*
  //   'id' attribute value checks; see section:- #3.4.2

  //   '0' - no  ^
  //   '1' - remove duplicate and/or invalid ones  *
  //   `word` - remove invalid ones and replace duplicate ones with new and unique ones based on the `word`; the admin-specified `word`, like 'my_', should begin with a letter (a-z) and can contain letters, digits, '.', '_', '-', and ':'.

  //   *xml:lang*
  //   Auto-adding 'xml:lang' attribute; see section:- #3.4.1

  //   '0' - no  *
  //   '1' - add if 'lang' attribute is present
  //   '2' - add if 'lang' attribute is present, and remove 'lang'  ~

  public function convertString($source, &$destination) {
    $spec = '';
    $conf = array();
    foreach ($this->cmd_options as $c) {
      if (isset($this->configuration[$c['name']])) {
        $conf[$c['name']] = $this->configuration[$c['name']];
      }
    }
    if (isset($conf['spec'])) {
      $spec = $conf['spec'];
      unset($conf['spec']);
    }
    $destination = hl::htmLawed($source, $conf, $spec);
    return $this;
  }

  protected function getHelpInstallation($os, $os_version) {
    $help = array(
      'title' => 'htmLawed',
      'url' => 'http://www.bioinformatics.org/phplabware/internal_utilities/htmLawed/',
      'description' => 'FileConverter uses a fork maintained at https://github.com/wittiws/HTMLawed',
    );
    return $help;
  }

  public function getVersionInfo() {
    $info = array(
      'php' => phpversion(),
      'htmlawed' => hl::hl_version(),
    );
    return $info;
  }

  public function isAvailable() {
    return class_exists('\\HtmLawed\\HtmLawed');
  }

}