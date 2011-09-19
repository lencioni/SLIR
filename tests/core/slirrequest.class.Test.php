<?php

require_once 'core/slir.class.php';
require_once 'core/slirrequest.class.php';

class SLIRRequestTest extends PHPUnit_Framework_TestCase
{
  protected $slir;

  protected function setUp()
  {
    $this->slir = new SLIR();
    $this->slir->getConfig();
  }

  /**
   * @test
   * @expectedException RuntimeException
   */
  public function setPathToNonexistentImage()
  {
    $request = new SLIRRequest();
    $request->path = 'path/to/nonexistant/image.jpg';
  }

  /**
   * @test
   * @expectedException RuntimeException
   */
  public function setPathToImageWithDoubleDots()
  {
    $request = new SLIRRequest();
    $request->path = 'path/to/../insecure/image.jpg';
  }

  /**
   * @test
   * @expectedException RuntimeException
   */
  public function setPathToImageWithColon()
  {
    $request = new SLIRRequest();
    $request->path = 'path/to/insecure/im:age.jpg';
  }

  /**
   * @test
   * @expectedException RuntimeException
   */
  public function setPathToImageWithGreaterThan()
  {
    $request = new SLIRRequest();
    $request->path = 'path/to/insecure/im>age.jpg';
  }

  /**
   * @test
   * @expectedException RuntimeException
   */
  public function setPathToImageWithLessThan()
  {
    $request = new SLIRRequest();
    $request->path = 'path/to/insecure/im<age.jpg';
  }

  /**
   * @test
   */
  public function setHeightWithString()
  {
    $request = new SLIRRequest();
    $request->height = '100';
    $this->assertSame($request->height, 100);
  }

  /**
   * @test
   */
  public function setHeightWithInteger()
  {
    $request = new SLIRRequest();
    $request->height = 100;
    $this->assertSame($request->height, 100);
  }

  /**
   * @test
   */
  public function setHeightWithFloatLowDecimal()
  {
    $request = new SLIRRequest();
    $request->height = 100.1;
    $this->assertSame($request->height, 100);
  }

  /**
   * @test
   */
  public function setHeightWithFloatHighDecimal()
  {
    $request = new SLIRRequest();
    $request->height = 100.9;
    $this->assertSame($request->height, 100);
  }

  /**
   * @test
   * @expectedException RuntimeException
   */
  public function setHeightWithNegativeInteger()
  {
    $request = new SLIRRequest();
    $request->height = -100;
  }

  /**
   * @test
   */
  public function setWidthWithString()
  {
    $request = new SLIRRequest();
    $request->width = '100';
    $this->assertSame($request->width, 100);
  }

  /**
   * @test
   */
  public function setWidthWithInteger()
  {
    $request = new SLIRRequest();
    $request->width = 100;
    $this->assertSame($request->width, 100);
  }

  /**
   * @test
   */
  public function setWidthWithFloatLowDecimal()
  {
    $request = new SLIRRequest();
    $request->width = 100.1;
    $this->assertSame($request->width, 100);
  }

  /**
   * @test
   */
  public function setWidthWithFloatHighDecimal()
  {
    $request = new SLIRRequest();
    $request->width = 100.9;
    $this->assertSame($request->width, 100);
  }

  /**
   * @test
   * @expectedException RuntimeException
   */
  public function setWidthWithNegativeInteger()
  {
    $request = new SLIRRequest();
    $request->width = -100;
  }

  /**
   * @test
   */
  public function setQualityWithString()
  {
    $request = new SLIRRequest();
    $request->quality = '50';
    $this->assertSame($request->quality, 50);
  }

  /**
   * @test
   */
  public function setQualityWithInteger()
  {
    $request = new SLIRRequest();
    $request->quality = 50;
    $this->assertSame($request->quality, 50);
  }

  /**
   * @test
   */
  public function setQualityWithFloatLowDecimal()
  {
    $request = new SLIRRequest();
    $request->quality = 50.1;
    $this->assertSame($request->quality, 50);
  }

  /**
   * @test
   */
  public function setQualityWithFloatHighDecimal()
  {
    $request = new SLIRRequest();
    $request->quality = 50.9;
    $this->assertSame($request->quality, 50);
  }

  /**
   * @test
   * @expectedException RuntimeException
   */
  public function setQualityWithNegativeInteger()
  {
    $request = new SLIRRequest();
    $request->quality = -1;
  }

  /**
   * @test
   * @expectedException RuntimeException
   */
  public function setQualityWithIntegerAbove100()
  {
    $request = new SLIRRequest();
    $request->quality = 101;
  }

  /**
   * @test
   */
  public function setProgressiveWithNumericStringOne()
  {
    $request = new SLIRRequest();
    $request->progressive = '1';
    $this->assertSame($request->progressive, true);
  }

  /**
   * @test
   */
  public function setProgressiveWithNumericStringZero()
  {
    $request = new SLIRRequest();
    $request->progressive = '0';
    $this->assertSame($request->progressive, false);
  }

  /**
   * @test
   */
  public function setProgressiveWithNumericStringGreaterThanOne()
  {
    $request = new SLIRRequest();
    $request->progressive = '100';
    $this->assertSame($request->progressive, true);
  }

  /**
   * @test
   */
  public function setProgressiveWithNumericStringLessThanZero()
  {
    $request = new SLIRRequest();
    $request->progressive = '-100';
    $this->assertSame($request->progressive, true);
  }

  /**
   * @test
   */
  public function setProgressiveWithNonNumericString()
  {
    $request = new SLIRRequest();
    $request->progressive = 'test';
    $this->assertSame($request->progressive, true);
  }

  /**
   * @test
   */
  public function setProgressiveWithNonNumericStringFalse()
  {
    $request = new SLIRRequest();
    $request->progressive = 'false';
    $this->assertSame($request->progressive, true);
  }

  /**
   * @test
   */
  public function setProgressiveWithEmptyString()
  {
    $request = new SLIRRequest();
    $request->progressive = '';
    $this->assertSame($request->progressive, false);
  }

  /**
   * @test
   */
  public function setProgressiveWithIntegerOne()
  {
    $request = new SLIRRequest();
    $request->progressive = 1;
    $this->assertSame($request->progressive, true);
  }

  /**
   * @test
   */
  public function setBackgroundWithLongHexUppercase()
  {
    $request = new SLIRRequest();
    $request->background = 'BADA55';
    $this->assertSame($request->background, 'BADA55');
  }

  /**
   * @test
   */
  public function setBackgroundWithLongHexLowercase()
  {
    $request = new SLIRRequest();
    $request->background = 'bada55';
    $this->assertSame($request->background, 'bada55');
  }

  /**
   * @test
   */
  public function setBackgroundWithLongHexMixedcase()
  {
    $request = new SLIRRequest();
    $request->background = 'BadA55';
    $this->assertSame($request->background, 'BadA55');
  }

  /**
   * @test
   */
  public function setBackgroundWithShortHexUppercase()
  {
    $request = new SLIRRequest();
    $request->background = 'FA8';
    $this->assertSame($request->background, 'FFAA88');
  }

  /**
   * @test
   */
  public function setBackgroundWithShortHexLowercase()
  {
    $request = new SLIRRequest();
    $request->background = 'fa8';
    $this->assertSame($request->background, 'ffaa88');
  }

  /**
   * @test
   */
  public function setBackgroundWithShortHexMixedcase()
  {
    $request = new SLIRRequest();
    $request->background = 'Fa8';
    $this->assertSame($request->background, 'FFaa88');
  }

  /**
   * @test
   * @expectedException RuntimeException
   */
  public function setBackgroundOneCharacter()
  {
    $request = new SLIRRequest();
    $request->background = 'a';
  }

  /**
   * @test
   * @expectedException RuntimeException
   */
  public function setBackgroundTwoCharacters()
  {
    $request = new SLIRRequest();
    $request->background = 'ef';
  }

  /**
   * @test
   * @expectedException RuntimeException
   */
  public function setBackgroundFourCharacters()
  {
    $request = new SLIRRequest();
    $request->background = 'FACE';
  }

  /**
   * @test
   * @expectedException RuntimeException
   */
  public function setBackgroundFiveCharacters()
  {
    $request = new SLIRRequest();
    $request->background = 'Ca5e5';
  }

  /**
   * @test
   * @expectedException RuntimeException
   */
  public function setBackgroundSevenCharacters()
  {
    $request = new SLIRRequest();
    $request->background = '1234567';
  }

  /**
   * @test
   */
  public function setBackgroundWithNonHexCharacters()
  {
    $request = new SLIRRequest();
    $request->background = '#BADA55';
    $this->assertSame($request->background, 'BADA55');
  }

}
