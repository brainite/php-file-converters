<?php
namespace Witti\FileConverter\Engine\Convert;

use Witti\FileConverter\Engine\EngineBase;
class WkHtmlToPdf extends EngineBase {
  // Set default properties
//   $this->set('html', 1)
//   ->set('base', 'http://' . $_SERVER['HTTP_HOST'] . '/');
//     // Determine whether this is a web request.
//     $optStr = '';
//     if ($this->get('web.request')) {
//       if (is_file($old)) {
//         $old = file_get_contents($old);
//       }
//       if (!preg_match('@^https?://@', $old)) {
//         throw new ErrorException("Invalid URL.");
//       }

//       // Add the cookies to the command.
//       if ($this->get('web.request.usercookies')) {
//         foreach ($_COOKIE as $k => $v) {
//           if ($v) {
//             $optStr .= sprintf(' --cookie %s %s ', escapeshellarg($k), escapeshellarg($v));
//           }
//         }
//       }
//     }
//     else {
//       if (strpos($old, '..') !== FALSE) {
//         throw new ErrorException("Directory dots are not allowed for file conversion.");
//       }
//     }

//     // Build the make options.
//     $validOpts = Array(
//         'margin-top',
//         'margin-right',
//         'margin-bottom',
//         'margin-left',
//         'title',
//         'user-style-sheet',
//         'javascript-delay',
//     );
//     foreach ($this->getByArray($validOpts) as $k => $v) {
//       if ($v) {
//         $optStr .= " --$k ";
//         if ($v != 1) {
//           $optStr .= $v . ' ';
//         }
//       }
//     }

//     // Convert the HTML using pisa
//     $oldE = escapeshellarg($old);
//     $newE = escapeshellarg($new);

//     // Using an older static version -- page-breaks work better than in 11.0rc1
//     // $cmd = "/go/bin/lib/wkhtmltopdf-0.11.0_rc1-static-amd64 -q $optStr $oldE $newE";
//     $cmd = "/go/bin/lib/wkhtmltopdf-0.10.0_rc2-static-amd64 -q $optStr $oldE $newE";
//     // $cmd = "/usr/bin/xvfb-run /usr/bin/wkhtmltopdf -q $optStr $oldE $newE";

  public function convertFile($source, $destination) {
    // One convert function is required to avoid recursion.
  }

  public function getHelpInstallation($os, $os_version) {
    return "This engine is a placeholder and is not yet ready for use.";
  }

  public function isAvailable() {
    return FALSE;
  }
}