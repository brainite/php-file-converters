<?php
/*
 * This file is part of the FileConverter package.
 *
 * (c) Greg Payne
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FileConverter\Configuration;

class ConfigurationDefaults extends ConfigurationBase {
  public function getAllConverters() {
    return $this->converters;
  }

  public function __construct(&$settings) {
    $settings = array_replace(array(
      // Ubuntu 12.04 LTS:
      //   /tmp
      //   Linux
      //   3.2.0-57-virtual
      // Windows 2008 R2 Standard
      //   C:\Users\USERNA~1\AppData\Local\Temp\3\
      //   Windows NT
      //   6.1
      'temp_dir' => sys_get_temp_dir() . '/fileconverter',
      'operating_system' => php_uname('s'),
      'operating_system_version' => php_uname('r'),
      // Override the default $PATH environmental variable.
      'env_path' => NULL,
    ), (array) $settings);
    if (!is_dir($settings['temp_dir'])) {
      @mkdir($settings['temp_dir'], 0755, TRUE);
    }

    // Attempt to get better OS information.
    // lsb_release is available on Ubun
    if ($settings['operating_system'] === 'Linux') {
      $lsb = trim(`which lsb_release`);
      if ($lsb !== '') {
        $lsb = escapeshellarg($lsb);
        $settings['operating_system'] = trim(`$lsb           -is`);
        $settings['operating_system_version'] = trim(`$lsb           -rs`);
      }
    }
    elseif ($settings['operating_system'] === 'Windows NT') {
      // Differentiate based on 32/64-bit but NOT based on build number.
      if (preg_match('@^build (\d+) \((Windows [^ ]+ )(.*?)\)$@', php_uname('v'), $arr)) {
        $settings['operating_system'] = preg_replace('@\s@s', '', $arr[2]);
        $settings['operating_system_version'] = preg_replace('@\s@s', '', $arr[3]
          . php_uname('m'));
        $settings['operating_system_version'] = strtr($settings['operating_system_version'], array(
          'Edition' => '',
          'ServicePack' => 'SP',
        ));
      }
    }

    // Configure default converter paths.
    // This does NOT mean that the converters are available.
    $this->converters = array(
      'doc->txt' => array(
        'catdoc:default' => array(
          '#engine' => 'Convert\\Catdoc',
        ),
      ),
      'html->eml' => array(
        'nativearchive:default' => array(
          '#engine' => 'Convert\\NativeArchive',
        ),
      ),
      'msg->eml' => array(
        'msgconvert:default' => array(
          '#engine' => 'Convert\\MsgConvert',
        ),
      ),
      'html->pdf' => array(
        'htmldoc:default' => array(
          '#engine' => 'Convert\\Htmldoc',
        ),
        'wkhtmltopdf:default' => array(
          '#engine' => 'Convert\\WkHtmlToPdf',
        ),
        'weasyprint:default' => array(
          '#engine' => 'Convert\\WeasyPrint',
        ),
        'xhtml2pdf:default' => array(
          '#engine' => 'Convert\\Xhtml2Pdf',
        ),
        'mpdf:default' => array(
          '#engine' => 'Convert\\Mpdf',
        ),
        'html->ps->pdf' => array(
          '#engine' => 'Chain',
          'chain' => 'html->ps->pdf',
        ),
      ),
      'html->(pdf|jpg)' => array(
        'phantomjs:default' => array(
          '#engine' => 'Convert\\PhantomJs',
        ),
      ),
      'wiki->html' => array(
        'wikiwym:default' => array(
          '#engine' => 'Convert\\Wikiwym',
          'code-block-lang' => 'php',
        ),
      ),
      'wiki->md' => array(
        'googlecode->github' => array(
          '#engine' => 'Chain',
          'chain' => 'wiki->html->md',
        ),
      ),
      '(html|markdown|textile|rst|docbook|latex)->(pdf|markdown|html|latex|context|mediawiki|textile|org|texinfo|docbook|docx|epub|mobi|asciidoc|rtf)' => array(
        'docverter:default' => array(
          '#engine' => 'Convert\\Docverter',
        ),
      ),
      '(html|txt|md|rst|dbk|textile|opml|tex|wiki)->(pdf|html|docx|odt|dbk|md|txt|opml|rtf|epub|epub3|fb2|man)' => array(
        'pandoc:default' => array(
          '#engine' => 'Convert\\Pandoc',
        ),
      ),
      '(jpg|png|gif|svg|tiff|wmf)->(jpg|png|gif|svg|tiff|wmf)' => array(
        'imagemagick:default' => array(
          '#engine' => 'Convert\\ImageMagick',
        ),
      ),
      'pdf->(jpg|png)' => array(
        'imagemagick:default' => array(
          '#engine' => 'Convert\\ImageMagick',
          "colorspace" => "sRGB",
          "flatten" => 1,
          "define" => 'pdf:use-cropbox=true',
        ),
      ),
      'pdf->(zip/jpg|directory/jpg)' => array(
        'imagemagick:default' => array(
          '#engine' => 'Convert\\ImageMagick',
          "colorspace" => "sRGB",
          // Double-resolution output
          'density' => '144',
        ),
      ),
      'pdf->(zip/png|directory/png)' => array(
        'imagemagick:default' => array(
          '#engine' => 'Convert\\ImageMagick',
          "colorspace" => "sRGB",
          // Supersampling with double-resolution output
          'density' => '288',
          'resize' => '50%',
        ),
      ),
      'ps->pdf' => array(
        'ghostscript:default' => array(
          '#engine' => 'Convert\\GhostScript',
        ),
      ),
      'pdf->pdf/grayscale' => array(
        'pdfgrayscale:default' => array(
          '#engine' => 'Convert\\PdfGrayscale',
        ),
      ),
      '(bib|doc|pdb|psw|rtf|ltx|sdw|sxw|txt|vor|html)->pdf' => array(
        'libreoffice:default' => array(
          '#engine' => 'Convert\\LibreOffice',
        ),
      ),
      '(ppt|pptx)->pdf' => array(
        'libreoffice:default' => array(
          '#engine' => 'Convert\\LibreOffice',
        ),
        'unoconv:default' => array(
          '#engine' => 'Convert\\Unoconv',
        ),
      ),
      '(ppt|pptx)->(directory/jpg|zip/jpg)' => array(
        'pptx->pdf->img' => array(
          '#engine' => 'Chain',
          'chain' => 'pptx->pdf->*',
        ),
      ),
      'pptx->json' => array(
        'nativemeta:default' => array(
          '#engine' => 'Convert\\NativeMeta',
        ),
      ),
      'pptx->(directory/slideshow|zip/slideshow)' => array(
        'nativeslideshow:schedule' => array(
          '#engine' => 'Convert\\NativeSlideshow',
          'mode' => 'schedule',
          'captions' => FALSE,
        ),
      ),
      'rtf->pdf' => array(
        'unoconv:default' => array(
          '#engine' => 'Convert\\Unoconv',
        ),
        'abiword:default' => array(
          '#engine' => 'Convert\\AbiWord',
        ),
        'rtf->ps->pdf' => array(
          '#engine' => 'Chain',
          'chain' => 'rtf->ps->pdf',
        ),
      ),
      'webp->(jpg|gif|svg|tiff|wmf)' => array(
        'webp->png->img' => array(
          '#engine' => 'Chain',
          'chain' => 'webp->png->*',
        ),
      ),
      'webp->png' => array(
        'webp:default' => array(
          '#engine' => 'Convert\\WebP',
        ),
      ),
      '(jpg|gif|svg|tiff|wmf)->webp' => array(
        'jpg->png->webp' => array(
          '#engine' => 'Chain',
          'chain' => '*->png->webp',
        ),
      ),
      'png->webp' => array(
        'webp:default' => array(
          '#engine' => 'Convert\\WebP',
        ),
      ),
      'rtf->ps' => array(
        'ted:default' => array(
          '#engine' => 'Convert\\Ted',
        ),
        'unrtf:default' => array(
          '#engine' => 'Convert\\Unrtf',
        ),
      ),
      'rtf~string' => array(
        'native:default' => array(
          '#engine' => 'ReplaceString\\ReplaceStringNative'
        ),
      ),
      'txt~string' => array(
        'native:default' => array(
          '#engine' => 'ReplaceString\\ReplaceStringNative'
        ),
      ),
      'html~optimize' => array(
        'email:archive' => array(
          "#engine" => "Optimize\\FcHtmlWord"
        ),
        'htmlawed:safe' => array(
          "#engine" => "Optimize\\HtmLawed",
          'comment' => '0',
          'elements' => 'a, abbr, acronym, address, area, b, bdo, big, blockquote, br, caption, center, cite, code, col, colgroup, dd, del, dfn, dir, div, dl, dt, em, font, h1, h2, h3, h4, h5, h6, hr, i, img, ins, kbd, label, legend, li, map, ol, p, pre, q, s, samp, small, span, strike, strong, sub, sup, table, tbody, td, textarea, tfoot, th, thead, tr, tt, u, ul',
          'tidy' => 1,
        ),
      ),
      'jpg~optimize' => array(
        'jpegoptim:default' => array(
          '#engine' => 'Optimize\\JpegOptim',
          'quiet' => TRUE,
          'strip-all' => TRUE,
        ),
      ),
      'pdf~optimize' => array(
        'pdftk:default' => array(
          '#engine' => 'Optimize\\Pdftk',
        ),
      ),
    );
    parent::__construct($settings);
  }

}
