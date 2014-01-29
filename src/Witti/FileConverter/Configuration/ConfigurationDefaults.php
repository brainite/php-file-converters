<?php
namespace Witti\FileConverter\Configuration;

class ConfigurationDefaults extends ConfigurationBase {
  public function getAllConverters() {
    return $this->converters;
  }

  public function __construct(&$settings) {
    $settings = array(
      // Ubuntu 12.04 LTS:
      //   /tmp
      //   Linux
      //   3.2.0-57-virtual
      // Windows 2008 R2 Standard
      //   C:\Users\USERNA~1\AppData\Local\Temp\3\
      //   Windows NT
      //   6.1
      'temp_dir' => sys_get_temp_dir(),
      'operating_system' => php_uname('s'),
      'operating_system_version' => php_uname('r'),
    );

    // Attempt to get better OS information.
    // lsb_release is available on Ubun
    if ($settings['operating_system'] === 'Linux') {
      $lsb = trim(`which lsb_release`);
      if ($lsb !== '') {
        $lsb = escapeshellarg($lsb);
        $settings['operating_system'] = trim(`$lsb     -is`);
        $settings['operating_system_version'] = trim(`$lsb     -rs`);
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
      'html->pdf' => array(
        'htmldoc:default' => array(
          '#engine' => 'Convert\\Htmldoc',
        ),
        'wkhtmltopdf:default' => array(
          '#engine' => 'Convert\\WkHtmlToPdf',
        ),
        'xhtml2pdf:default' => array(
          '#engine' => 'Convert\\Xhtml2Pdf',
        ),
        'html->ps->pdf' => array(
          '#engine' => 'Chain',
          'chain' => 'html->ps->pdf',
        ),
      ),
      'pdf->jpg' => array(
        'imagemagick:default' => array(
          '#engine' => 'Convert\\ImageMagick'
        ),
      ),
      'ps->pdf' => array(
        'ghostscript:default' => array(
          '#engine' => 'Convert\\GhostScript',
        ),
      ),
      '(bib|doc|pdb|psw|rtf|ltx|sdw|sxw|txt|vor|html)->pdf' => array(
        'libreoffice:default' => array(
          '#engine' => 'Convert\\LibreOffice',
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