<?php
require_once realpath(__DIR__ . '/../slirTestCase.class.php');
require_once realpath(__DIR__ . '/../../core/slirrequest.class.php');

class SLIRRequestTest extends SLIRTestCase
{

  /**
   * @test
   * @expectedException RuntimeException
   * @expectedExceptionMessage Image does not exist
   */
  public function setPathToNonexistentImageWithoutDefaultImage()
  {
    $request = new SLIRRequest();
    $request->path = 'path/to/nonexistant/image.jpg';
  }

  /**
   * @test
   * @expectedException RuntimeException
   * @expectedExceptionMessage Image does not exist
   */
  public function setPathToNonexistentImageWithNonexistentDefaultImage()
  {
    $request = new SLIRRequest();
    SLIRConfig::$defaultImagePath = 'default.jpg';
    $request->path = 'path/to/nonexistant/image.jpg';
    SLIRConfig::$defaultImagePath = null;
  }

  /**
   * @test
   */
  public function setPathToNonexistentImageWithExistentDefaultImage()
  {
    $request = new SLIRRequest();
    SLIRConfig::$defaultImagePath = 'slir/Test/images/camera-logo.png';
    $request->path = 'path/to/nonexistent/image.jpg';
    $this->assertSame($request->path, '/slir/Test/images/camera-logo.png');
    SLIRConfig::$defaultImagePath = null;
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
   * @expectedExceptionMessage Image path may not contain
   */
  public function setPathToImageWithColon()
  {
    $request = new SLIRRequest();
    $request->path = 'path/to/in:secure/image.jpg';
  }

  /**
   * @test
   * @expectedException RuntimeException
   * @expectedExceptionMessage Image path may not contain
   */
  public function setPathToImageWithGreaterThan()
  {
    $request = new SLIRRequest();
    $request->path = 'path/to/insecure/im>age.jpg';
  }

  /**
   * @test
   * @expectedException RuntimeException
   * @expectedExceptionMessage Image path may not contain
   */
  public function setPathToImageWithLessThan()
  {
    $request = new SLIRRequest();
    $request->path = 'path/to/insecure/im<age.jpg';
  }

  /**
   * @test
   */
  public function setPathToExistentImage()
  {
    $request = new SLIRRequest();
    $request->path = 'slir/Test/images/camera-logo.png';
    $this->assertSame($request->path, '/slir/Test/images/camera-logo.png');
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
   * @expectedExceptionMessage Height must be greater than 0
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
   * @expectedExceptionMessage Width must be greater than 0
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
    $this->assertFalse($request->isQuality());
    $request->quality = '50';
    $this->assertSame($request->quality, 50);
    $this->assertTrue($request->isQuality());
  }

  /**
   * @test
   */
  public function setQualityWithInteger()
  {
    $request = new SLIRRequest();
    $this->assertFalse($request->isQuality());
    $request->quality = 50;
    $this->assertSame($request->quality, 50);
    $this->assertTrue($request->isQuality());
  }

  /**
   * @test
   */
  public function setQualityWithFloatLowDecimal()
  {
    $request = new SLIRRequest();
    $this->assertFalse($request->isQuality());
    $request->quality = 50.1;
    $this->assertSame($request->quality, 50);
    $this->assertTrue($request->isQuality());
  }

  /**
   * @test
   */
  public function setQualityWithFloatHighDecimal()
  {
    $request = new SLIRRequest();
    $this->assertFalse($request->isQuality());
    $request->quality = 50.9;
    $this->assertSame($request->quality, 50);
    $this->assertTrue($request->isQuality());
  }

  /**
   * @test
   * @expectedException RuntimeException
   * @expectedExceptionMessage Quality must be between 0 and 100
   */
  public function setQualityWithNegativeInteger()
  {
    $request = new SLIRRequest();
    $request->quality = -1;
    $this->assertFalse($request->isQuality());
  }

  /**
   * @test
   * @expectedException RuntimeException
   * @expectedExceptionMessage Quality must be between 0 and 100
   */
  public function setQualityWithIntegerAbove100()
  {
    $request = new SLIRRequest();
    $request->quality = 101;
    $this->assertFalse($request->isQuality());
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
    $this->assertFalse($request->isBackground());
    $request->background = 'BADA55';
    $this->assertSame($request->background, 'BADA55');
    $this->assertTrue($request->isBackground());
  }

  /**
   * @test
   */
  public function setBackgroundWithLongHexLowercase()
  {
    $request = new SLIRRequest();
    $this->assertFalse($request->isBackground());
    $request->background = 'bada55';
    $this->assertSame($request->background, 'bada55');
    $this->assertTrue($request->isBackground());
  }

  /**
   * @test
   */
  public function setBackgroundWithLongHexMixedcase()
  {
    $request = new SLIRRequest();
    $this->assertFalse($request->isBackground());
    $request->background = 'BadA55';
    $this->assertSame($request->background, 'BadA55');
    $this->assertTrue($request->isBackground());
  }

  /**
   * @test
   */
  public function setBackgroundWithShortHexUppercase()
  {
    $request = new SLIRRequest();
    $this->assertFalse($request->isBackground());
    $request->background = 'FA8';
    $this->assertSame($request->background, 'FFAA88');
    $this->assertTrue($request->isBackground());
  }

  /**
   * @test
   */
  public function setBackgroundWithShortHexLowercase()
  {
    $request = new SLIRRequest();
    $this->assertFalse($request->isBackground());
    $request->background = 'fa8';
    $this->assertSame($request->background, 'ffaa88');
    $this->assertTrue($request->isBackground());
  }

  /**
   * @test
   */
  public function setBackgroundWithShortHexMixedcase()
  {
    $request = new SLIRRequest();
    $this->assertFalse($request->isBackground());
    $request->background = 'Fa8';
    $this->assertSame($request->background, 'FFaa88');
    $this->assertTrue($request->isBackground());
  }

  /**
   * @test
   * @expectedException RuntimeException
   * @expectedExceptionMessage Background fill color must be in hexadecimal format
   */
  public function setBackgroundOneCharacter()
  {
    $request = new SLIRRequest();
    $request->background = 'a';
    $this->assertFalse($request->isBackground());
  }

  /**
   * @test
   * @expectedException RuntimeException
   * @expectedExceptionMessage Background fill color must be in hexadecimal format
   */
  public function setBackgroundTwoCharacters()
  {
    $request = new SLIRRequest();
    $request->background = 'ef';
    $this->assertFalse($request->isBackground());
  }

  /**
   * @test
   * @expectedException RuntimeException
   * @expectedExceptionMessage Background fill color must be in hexadecimal format
   */
  public function setBackgroundFourCharacters()
  {
    $request = new SLIRRequest();
    $request->background = 'FACE';
    $this->assertFalse($request->isBackground());
  }

  /**
   * @test
   * @expectedException RuntimeException
   * @expectedExceptionMessage Background fill color must be in hexadecimal format
   */
  public function setBackgroundFiveCharacters()
  {
    $request = new SLIRRequest();
    $request->background = 'Ca5e5';
    $this->assertFalse($request->isBackground());
  }

  /**
   * @test
   * @expectedException RuntimeException
   * @expectedExceptionMessage Background fill color must be in hexadecimal format
   */
  public function setBackgroundSevenCharacters()
  {
    $request = new SLIRRequest();
    $request->background = '1234567';
    $this->assertFalse($request->isBackground());
  }

  /**
   * @test
   */
  public function setBackgroundWithNonHexCharacters()
  {
    $request = new SLIRRequest();
    $this->assertFalse($request->isBackground());
    $request->background = '#BADA55';
    $this->assertSame($request->background, 'BADA55');
    $this->assertTrue($request->isBackground());
  }

  /**
   * @test
   */
  public function setCropRatioWithXDelimiter()
  {
    $request = new SLIRRequest();
    $this->assertFalse($request->isCropping());
    $request->cropRatio = '2x1';
    $this->assertSame($request->cropRatio, array('width' => 2.0, 'height' => 1.0, 'ratio' => 2.0));
    $this->assertTrue($request->isCropping());
  }

  /**
   * @test
   */
  public function setCropRatioWithPeriodDelimiter()
  {
    $request = new SLIRRequest();
    $this->assertFalse($request->isCropping());
    $request->cropRatio = '2.1';
    $this->assertSame($request->cropRatio, array('width' => 2.0, 'height' => 1.0, 'ratio' => 2.0));
    $this->assertTrue($request->isCropping());
  }

  /**
   * @test
   */
  public function setCropRatioWithColonDelimiter()
  {
    $request = new SLIRRequest();
    $this->assertFalse($request->isCropping());
    $request->cropRatio = '2:1';
    $this->assertSame($request->cropRatio, array('width' => 2.0, 'height' => 1.0, 'ratio' => 2.0));
    $this->assertTrue($request->isCropping());
  }

  /**
   * @test
   */
  public function setCropRatioWithCropperSameDelimiters()
  {
    $request = new SLIRRequest();
    $this->assertFalse($request->isCropping());
    $request->cropRatio = '2x1xsmart';
    $this->assertSame($request->cropRatio, array('width' => 2.0, 'height' => 1.0, 'ratio' => 2.0));
    $this->assertSame($request->cropper, 'smart');
    $this->assertTrue($request->isCropping());
  }

  /**
   * @test
   */
  public function setCropRatioWithCropperMixedDelimiters()
  {
    $request = new SLIRRequest();
    $this->assertFalse($request->isCropping());
    $request->cropRatio = '2x1.smart';
    $this->assertSame($request->cropRatio, array('width' => 2.0, 'height' => 1.0, 'ratio' => 2.0));
    $this->assertSame($request->cropper, 'smart');
    $this->assertTrue($request->isCropping());
  }

  /**
   * @test
   */
  public function setCropRatioWithExtraInformation()
  {
    $request = new SLIRRequest();
    $this->assertFalse($request->isCropping());
    $request->cropRatio = '2x1xsmartxbonusxinformation';
    $this->assertSame($request->cropRatio, array('width' => 2.0, 'height' => 1.0, 'ratio' => 2.0));
    $this->assertSame($request->cropper, 'smart');
    $this->assertTrue($request->isCropping());
  }

  /**
   * @test
   */
  public function setCropRatioWithLargeWidth()
  {
    $request = new SLIRRequest();
    $this->assertFalse($request->isCropping());
    $request->cropRatio = '2000000x1';
    $this->assertSame($request->cropRatio, array('width' => 2000000.0, 'height' => 1.0, 'ratio' => 2000000.0));
    $this->assertTrue($request->isCropping());
  }

  /**
   * @test
   */
  public function setCropRatioWithLargeHeight()
  {
    $request = new SLIRRequest();
    $this->assertFalse($request->isCropping());
    $request->cropRatio = '1x2000000';
    $this->assertSame($request->cropRatio, array('width' => 1.0, 'height' => 2000000.0, 'ratio' => 0.0000005));
    $this->assertTrue($request->isCropping());
  }

  /**
   * @test
   * @expectedException RuntimeException
   * @expectedExceptionMessage Crop ratio must not contain a zero
   */
  public function setCropRatioWithZeroWidth()
  {
    $request = new SLIRRequest();
    $request->cropRatio = '100x0';
    $this->assertFalse($request->isCropping());
  }

  /**
   * @test
   * @expectedException RuntimeException
   * @expectedExceptionMessage Crop ratio must not contain a zero
   */
  public function setCropRatioWithZeroHeight()
  {
    $request = new SLIRRequest();
    $request->cropRatio = '0x100';
    $this->assertFalse($request->isCropping());
  }

  /**
   * @test
   * @expectedException RuntimeException
   * @expectedExceptionMessage Crop ratio must be in [width]x[height] format
   */
  public function setCropRatioWithNoHeight()
  {
    $request = new SLIRRequest();
    $request->cropRatio = '100';
    $this->assertFalse($request->isCropping());
  }

  /**
   * @test
   * @expectedException RuntimeException
   * @expectedExceptionMessage Source image was not specified
   */
  public function initializeNoImage()
  {
    $request = new SLIRRequest();

    $_SERVER['REQUEST_URI'] = '/slir/w100/';
    $request->initialize();
  }

  /**
   * @test
   * @expectedException RuntimeException
   * @expectedExceptionMessage Not enough parameters were given
   */
  public function initializeNoParameters()
  {
    $request = new SLIRRequest();

    $_SERVER['REQUEST_URI'] = '/';
    $request->initialize();
  }

  /**
   * @test
   * @expectedException RuntimeException
   * @expectedExceptionMessage Image does not exist
   */
  public function initializeNonexistentImage()
  {
    $request = new SLIRRequest();

    $_SERVER['REQUEST_URI'] = '/slir/w100/path/to/nonexistent/image.jpg';
    $request->initialize();
  }

  /**
   * @test
   */
  public function initializeExistentImageOnlyWidth()
  {
    $request = new SLIRRequest();

    $_SERVER['REQUEST_URI'] = '/slir/w100/slir/Test/images/camera-logo.png';
    $request->initialize();

    $this->assertSame($request->width, 100);
    $this->assertSame($request->path, '/slir/Test/images/camera-logo.png');
  }

  /**
   * @test
   */
  public function initializeLotsOfParameters()
  {
    $request = new SLIRRequest();

    $_SERVER['REQUEST_URI'] = '/slir/w100-h101-c2.1-q15-p1/slir/Test/images/camera-logo.png';
    $request->initialize();

    $this->assertSame($request->width, 100);
    $this->assertSame($request->height, 101);
    $this->assertSame($request->cropRatio, array('width' => 2.0, 'height' => 1.0, 'ratio' => 2.0));
    $this->assertSame($request->quality, 15);
    $this->assertSame($request->progressive, true);
    $this->assertSame($request->path, '/slir/Test/images/camera-logo.png');
  }

  /**
   * @test
   */
  public function initializeDefaultImageOnlyWidth()
  {
    $request = new SLIRRequest();

    SLIRConfig::$defaultImagePath = '/slir/Test/images/camera-logo.png';

    $_SERVER['REQUEST_URI'] = '/slir/w100/path/to/nonexistent/image.png';
    $request->initialize();

    $this->assertSame($request->width, 100);
    $this->assertSame($request->path, '/slir/Test/images/camera-logo.png');

    SLIRConfig::$defaultImagePath = null;
  }

  /**
   * @test
   */
  public function initializeUnspecifiedImageWithDefaultImageOnlyWidth()
  {
    $request = new SLIRRequest();

    SLIRConfig::$defaultImagePath = '/slir/Test/images/camera-logo.png';

    $_SERVER['REQUEST_URI'] = '/slir/w100/';
    $request->initialize();

    $this->assertSame($request->width, 100);
    $this->assertSame($request->path, '/slir/Test/images/camera-logo.png');

    SLIRConfig::$defaultImagePath = null;
  }

  /**
   * @test
   * @expectedException RuntimeException
   * @expectedExceptionMessage Source image was not specified
   */
  public function initializeForceQueryStringNoParameters()
  {
    SLIRConfig::$forceQueryString = true;
    $request = new SLIRRequest();

    // This should have no effect
    $_SERVER['REQUEST_URI'] = '/slir/w100/slir/Test/images/camera-logo.png';
    $request->initialize();

    SLIRConfig::$forceQueryString = false;
  }

   /**
   * @test
   */
  public function initializeForceQueryStringWidthAndImage()
  {
    SLIRConfig::$forceQueryString = true;
    $request = new SLIRRequest();

    // This should have no effect
    $_SERVER['REQUEST_URI'] = '/slir/w100/nonexistent/image.png';

    $_GET       = array();
    $_GET['i']  = '/slir/Test/images/camera-logo.png';
    $_GET['w']  = '100';

    $request->initialize();

    $this->assertSame($request->path, $_GET['i']);
    $this->assertSame($request->width, 100);

    SLIRConfig::$forceQueryString = false;
  }

  /**
   * @test
   */
  public function initializeUnforcedQueryStringWidthAndImage()
  {
    SLIRConfig::$forceQueryString = false;
    $request = new SLIRRequest();

    $_SERVER['QUERY_STRING'] = 'i=/slir/Test/images/camera-logo.png&w=100';
    $_GET       = array();
    $_GET['i']  = '/slir/Test/images/camera-logo.png';
    $_GET['w']  = '100';

    $request->initialize();

    $this->assertSame($request->path, $_GET['i']);
    $this->assertSame($request->width, 100);
  }

  /**
   * @test
   */
  public function destruct()
  {
    $request = new SLIRRequest();
    $request->__destruct();

    $this->assertFalse(isset($request->path));
    $this->assertFalse(isset($request->width));
    $this->assertFalse(isset($request->height));
    $this->assertFalse(isset($request->cropRatio));
    $this->assertFalse(isset($request->cropper));
    $this->assertFalse(isset($request->quality));
    $this->assertFalse(isset($request->progressive));
    $this->assertFalse(isset($request->background));
    $this->assertFalse(isset($request->isUsingDefaultImagePath));
    $this->assertTrue(isset($request));
  }

}
