<?php
/*
 * This file is part of the FileConverter package.
 *
 * (c) Greg Payne
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FileConverter\Tests;

class DefaultConfigTest extends \PHPUnit_Framework_TestCase {
  /**
   * @dataProvider getConfigurationDefaults
   */
  public function testConfigurationDefaults($convert_path, $expected) {
    $fc = \FileConverter\FileConverter::factory(FALSE);
    $engines = $fc->getEngines($convert_path, NULL, FALSE);
    $test = array();
    foreach ($engines as $engine) {
      $test[get_class($engine)] = $engine->getConfiguration();
    }

    // JSON-encode the arrays so that sort order is tested as well.
    $this->assertEquals(json_encode($test), json_encode($expected));
  }

  public function getConfigurationDefaults() {
    return array(
      array(
        'pdf->jpg',
        array(
          'FileConverter\Engine\Convert\ImageMagick' => array(
            '#engine' => 'Convert\\ImageMagick',
            "colorspace" => "sRGB",
            "flatten" => 1,
          ),
        ),
      ),
      array(
        'rtf->pdf',
        array(
          'FileConverter\Engine\Convert\LibreOffice' => array(
            '#engine' => 'Convert\\LibreOffice',
          ),
          'FileConverter\Engine\Convert\Unoconv' => array(
            '#engine' => 'Convert\\Unoconv',
          ),
          'FileConverter\Engine\Convert\AbiWord' => array(
            '#engine' => 'Convert\\AbiWord',
          ),
          'FileConverter\Engine\Chain' => array(
            '#engine' => 'Chain',
            'chain' => 'rtf->ps->pdf',
          ),
        )
      )
    );
  }

}