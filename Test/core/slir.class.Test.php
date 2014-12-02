<?php
require_once realpath(__DIR__ . '/../slirTestCase.class.php');

class SLIRTest extends SLIRTestCase
{
  /**
   * @param string $header
   * @return void
   */
  private function assertHeaderSent($header)
  {
    $this->assertContains($header, $this->slir->getHeaders());
  }

  /**
   * @param string $header
   * @return void
   */
  private function assertHeaderNotSent($header)
  {
    $this->assertNotContains($header, $this->slir->getHeaders());
  }

  /**
   * @test
   */
  public function escapeOutputBuffering()
  {
    ob_start();
    ob_start();
    ob_start();
    $inceptionLevel = ob_get_level();

    $this->slir->escapeOutputBuffering();

    $this->assertLessThan($inceptionLevel, ob_get_level());
  }

  /**
   * @test
   * @expectedException RuntimeException
   * @expectedExceptionMessage Not enough parameters
   */
  public function processRequestFromURLNoParameters()
  {
    $_SERVER['REQUEST_URI'] = '';
    $this->slir->processRequestFromURL();
  }

  /**
   * @test
   *
   * @return string image output
   */
  public function processUncachedRequestFromURLWithOnlyWidthSpecified()
  {
    $_SERVER['REQUEST_URI'] = '/slir/w50/slir/Test/images/camera-logo.png';

    $this->slir->uncache();

    $this->assertFalse($this->slir->isRequestCached());
    $this->assertFalse($this->slir->isRenderedCached());

    ob_start();
    $this->slir->processRequestFromURL();
    $output = ob_get_clean();

    $this->assertHeaderNotSent('HTTP/1.1 304 Not Modified');
    $this->assertTrue($this->slir->isRequestCached());
    $this->assertTrue($this->slir->isRenderedCached());

    $image = imagecreatefromstring($output);
    $this->assertInternalType('resource', $image);
    $this->assertSame(50, imagesx($image));

    imagedestroy($image);
    unset($image);

    return $output;
  }

  /**
   * @test
   *
   * @return string image output
   */
  public function processUncachedRequestFromURLWithOnlyHeightSpecified()
  {
    $_SERVER['REQUEST_URI'] = '/slir/h50/slir/Test/images/camera-logo.png';

    $this->slir->uncache();

    $this->assertFalse($this->slir->isRequestCached());
    $this->assertFalse($this->slir->isRenderedCached());

    ob_start();
    $this->slir->processRequestFromURL();
    $output = ob_get_clean();

    $this->assertHeaderNotSent('HTTP/1.1 304 Not Modified');
    $this->assertTrue($this->slir->isRequestCached());
    $this->assertTrue($this->slir->isRenderedCached());

    $image = imagecreatefromstring($output);
    $this->assertInternalType('resource', $image);
    $this->assertSame(50, imagesy($image));

    imagedestroy($image);
    unset($image);

    return $output;
  }

  /**
   * @test
   *
   * @return string image output
   */
  public function processUncachedRequestFromURLWithOnlyQualitySpecified()
  {
    $_SERVER['REQUEST_URI'] = '/slir/q10/slir/Test/images/camera-logo.png';

    $this->slir->uncache();

    $this->assertFalse($this->slir->isRequestCached());
    $this->assertFalse($this->slir->isRenderedCached());

    ob_start();
    $this->slir->processRequestFromURL();
    $output = ob_get_clean();

    $this->assertHeaderNotSent('HTTP/1.1 304 Not Modified');
    $this->assertTrue($this->slir->isRequestCached());
    $this->assertTrue($this->slir->isRenderedCached());

    $image = imagecreatefromstring($output);
    $this->assertInternalType('resource', $image);

    imagedestroy($image);
    unset($image);

    return $output;
  }

  /**
   * @test
   * @depends processUncachedRequestFromURLWithOnlyWidthSpecified
   *
   * @param string $uncachedImageOutput
   */
  public function processRequestThatShouldBeServedFromTheRequestCache($uncachedImageOutput)
  {
    $_SERVER['REQUEST_URI'] = '/slir/w50/slir/Test/images/camera-logo.png';

    $this->assertTrue($this->slir->isRequestCached());
    $this->assertTrue($this->slir->isRenderedCached());

    ob_start();
    $this->slir->processRequestFromURL();
    $output = ob_get_clean();

    $this->assertHeaderNotSent('HTTP/1.1 304 Not Modified');

    $this->assertSame($uncachedImageOutput, $output);
  }

  /**
   * @test
   * @depends processUncachedRequestFromURLWithOnlyWidthSpecified
   *
   * @param string $uncachedImageOutput
   */
  public function processRequestThatShouldBeServedFromTheRenderedCache($uncachedImageOutput)
  {

    $_SERVER['REQUEST_URI'] = '/slir/w50-h10000/slir/Test/images/camera-logo.png';

    $this->slir->uncacheRequest();

    $this->assertFalse($this->slir->isRequestCached());
    $this->assertTrue($this->slir->isRenderedCached());

    ob_start();
    $this->slir->processRequestFromURL();
    $output = ob_get_clean();

    $this->assertHeaderNotSent('HTTP/1.1 304 Not Modified');
    $this->assertTrue($this->slir->isRequestCached());

    $this->assertSame($uncachedImageOutput, $output);
  }

  /**
   * @test
   */
  public function processRequestThatShouldServeSourceImage()
  {
    $_SERVER['REQUEST_URI'] = '/slir/w99999-q100/slir/Test/images/camera-logo.png';

    $this->assertFalse($this->slir->isRequestCached());
    $this->assertFalse($this->slir->isRenderedCached());

    ob_start();
    $this->slir->processRequestFromURL();
    $output = ob_get_clean();

    $this->assertHeaderNotSent('HTTP/1.1 304 Not Modified');
    $this->assertSame(file_get_contents(realpath(__DIR__ . '/../images/camera-logo.png')), $output);

    $this->assertFalse($this->slir->isRequestCached());
    $this->assertFalse($this->slir->isRenderedCached());
  }

  /**
   * @test
   * @depends processUncachedRequestFromURLWithOnlyWidthSpecified
   */
  public function processRequestThatShouldBeCachedInTheBrowser()
  {
    $_SERVER['HTTP_IF_MODIFIED_SINCE'] = gmdate('D, d M Y H:i:s', time() + 100) . ' GMT';

    $_SERVER['REQUEST_URI'] = '/slir/w50/slir/Test/images/camera-logo.png';

    ob_start();
    $this->slir->processRequestFromURL();
    $output = ob_get_clean();

    $this->assertHeaderSent('HTTP/1.1 304 Not Modified');
    $this->assertSame('', $output);

    unset($_SERVER['HTTP_IF_MODIFIED_SINCE']);
  }

  /**
   * @test
   *
   * @return string image output
   */
  public function processUncachedRequestFromURLWithHeightAndWidthSpecified()
  {
    $_SERVER['REQUEST_URI'] = '/slir/w50-h50/slir/Test/images/camera-logo.png';

    $this->slir->uncache();

    $this->assertFalse($this->slir->isRequestCached());
    $this->assertFalse($this->slir->isRenderedCached());

    ob_start();
    $this->slir->processRequestFromURL();
    $output = ob_get_clean();

    $this->assertHeaderNotSent('HTTP/1.1 304 Not Modified');
    $this->assertTrue($this->slir->isRequestCached());
    $this->assertTrue($this->slir->isRenderedCached());

    $image = imagecreatefromstring($output);
    $this->assertInternalType('resource', $image);
    $this->assertLessThan(50, imagesy($image));
    $this->assertSame(50, imagesx($image));

    imagedestroy($image);
    unset($image);

    return $output;
  }

  /**
   * @test
   *
   * @return string image output
   */
  public function processUncachedRequestFromURLWithSquareCropSpecified()
  {
    $_SERVER['REQUEST_URI'] = '/slir/w50-c1.1/slir/Test/images/camera-logo.png';

    $this->slir->uncache();

    $this->assertFalse($this->slir->isRequestCached());
    $this->assertFalse($this->slir->isRenderedCached());

    ob_start();
    $this->slir->processRequestFromURL();
    $output = ob_get_clean();

    $this->assertHeaderNotSent('HTTP/1.1 304 Not Modified');
    $this->assertTrue($this->slir->isRequestCached());
    $this->assertTrue($this->slir->isRenderedCached());

    $image = imagecreatefromstring($output);
    $this->assertInternalType('resource', $image);
    $this->assertSame(imagesx($image), imagesy($image));

    imagedestroy($image);
    unset($image);

    return $output;
  }

  /**
   * @test
   *
   * @return string image output
   */
  public function processUncachedRequestFromURLWithWideCropSpecified()
  {
    $_SERVER['REQUEST_URI'] = '/slir/w50-c2.1/slir/Test/images/camera-logo.png';

    $this->slir->uncache();

    $this->assertFalse($this->slir->isRequestCached());
    $this->assertFalse($this->slir->isRenderedCached());

    ob_start();
    $this->slir->processRequestFromURL();
    $output = ob_get_clean();

    $this->assertHeaderNotSent('HTTP/1.1 304 Not Modified');
    $this->assertTrue($this->slir->isRequestCached());
    $this->assertTrue($this->slir->isRenderedCached());

    $image = imagecreatefromstring($output);
    $this->assertInternalType('resource', $image);
    $this->assertSame(50, imagesx($image));
    $this->assertSame(25, imagesy($image));

    imagedestroy($image);
    unset($image);

    return $output;
  }

  /**
   * @test
   *
   * @return string image output
   */
  public function processUncachedRequestFromURLWithTallCropSpecified()
  {
    $_SERVER['REQUEST_URI'] = '/slir/w50-c1.2/slir/Test/images/camera-logo.png';

    $this->slir->uncache();

    $this->assertFalse($this->slir->isRequestCached());
    $this->assertFalse($this->slir->isRenderedCached());

    ob_start();
    $this->slir->processRequestFromURL();
    $output = ob_get_clean();

    $this->assertHeaderNotSent('HTTP/1.1 304 Not Modified');
    $this->assertTrue($this->slir->isRequestCached());
    $this->assertTrue($this->slir->isRenderedCached());

    $image = imagecreatefromstring($output);
    $this->assertInternalType('resource', $image);
    $this->assertSame(50, imagesx($image));
    $this->assertSame(100, imagesy($image));

    imagedestroy($image);
    unset($image);

    return $output;
  }

  /**
   * @test
   *
   * @return string image output
   */
  public function processUncachedRequestFromURLWithSquareCropCenteredSpecified()
  {
    $_SERVER['REQUEST_URI'] = '/slir/w50-c1.1.centered/slir/Test/images/camera-logo.png';

    $this->slir->uncache();

    $this->assertFalse($this->slir->isRequestCached());
    $this->assertFalse($this->slir->isRenderedCached());

    ob_start();
    $this->slir->processRequestFromURL();
    $output = ob_get_clean();

    $this->assertHeaderNotSent('HTTP/1.1 304 Not Modified');
    $this->assertTrue($this->slir->isRequestCached());
    $this->assertTrue($this->slir->isRenderedCached());

    $image = imagecreatefromstring($output);
    $this->assertInternalType('resource', $image);
    $this->assertSame(imagesx($image), imagesy($image));

    imagedestroy($image);
    unset($image);

    return $output;
  }

  /**
   * @test
   * @depends processUncachedRequestFromURLWithSquareCropCenteredSpecified
   *
   * @param string $centerCroppedImage
   * @return string image output
   */
  public function processUncachedRequestFromURLWithSquareCropSmartSpecified($centerCroppedImage)
  {
    $_SERVER['REQUEST_URI'] = '/slir/w50-c1.1.smart/slir/Test/images/camera-logo.png';

    $this->slir->uncache();

    $this->assertFalse($this->slir->isRequestCached());
    $this->assertFalse($this->slir->isRenderedCached());

    ob_start();
    $this->slir->processRequestFromURL();
    $output = ob_get_clean();

    $this->assertHeaderNotSent('HTTP/1.1 304 Not Modified');
    $this->assertTrue($this->slir->isRequestCached());
    $this->assertTrue($this->slir->isRenderedCached());

    $this->assertNotSame($centerCroppedImage, $output);

    $image = imagecreatefromstring($output);
    $this->assertInternalType('resource', $image);
    $this->assertSame(50, imagesx($image));
    $this->assertSame(50, imagesy($image));

    imagedestroy($image);
    unset($image);

    return $output;
  }

  /**
   * @test
   *
   * @return string image output
   */
  public function processUncachedRequestFromURLWithOnlyBlueBackgroundFill()
  {
    $_SERVER['REQUEST_URI'] = '/slir/b00f/slir/Test/images/camera-logo.png';

    $this->slir->uncache();

    $this->assertFalse($this->slir->isRequestCached());
    $this->assertFalse($this->slir->isRenderedCached());

    ob_start();
    $this->slir->processRequestFromURL();
    $output = ob_get_clean();

    $this->assertHeaderNotSent('HTTP/1.1 304 Not Modified');
    $this->assertTrue($this->slir->isRequestCached());
    $this->assertTrue($this->slir->isRenderedCached());

    $image = imagecreatefromstring($output);
    $this->assertInternalType('resource', $image);

    $color = imagecolorat($image, 0, 0);
    $rgb   = imagecolorsforindex($image, $color);
    $this->assertSame(0, $rgb['red']);
    $this->assertSame(0, $rgb['green']);
    $this->assertSame(255, $rgb['blue']);
    $this->assertSame(0, $rgb['alpha']);

    imagedestroy($image);
    unset($image);

    return $output;
  }

  /**
   * @test
   *
   * @return string image output
   */
  public function processUncachedRequestFromURLWithOnlyWidthSpecifiedForJPEG()
  {
    $_SERVER['REQUEST_URI'] = '/slir/w50/slir/Test/images/joe-lencioni.jpg';

    $this->slir->uncache();

    $this->assertFalse($this->slir->isRequestCached());
    $this->assertFalse($this->slir->isRenderedCached());

    ob_start();
    $this->slir->processRequestFromURL();
    $output = ob_get_clean();

    $this->assertHeaderNotSent('HTTP/1.1 304 Not Modified');
    $this->assertTrue($this->slir->isRequestCached());
    $this->assertTrue($this->slir->isRenderedCached());

    $image = imagecreatefromstring($output);
    $this->assertInternalType('resource', $image);
    $this->assertSame(50, imagesx($image));

    imagedestroy($image);
    unset($image);

    return $output;
  }

  /**
   * @test
   * @depends processUncachedRequestFromURLWithOnlyWidthSpecifiedForJPEG
   *
   * @param string $defaultQualityImage
   * @return string image output
   */
  public function processUncachedRequestFromURLWithWidthAndQualitySpecifiedForJPEG($defaultQualityImage)
  {
    $_SERVER['REQUEST_URI'] = '/slir/w50-q10/slir/Test/images/joe-lencioni.jpg';

    $this->slir->uncache();

    $this->assertFalse($this->slir->isRequestCached());
    $this->assertFalse($this->slir->isRenderedCached());

    ob_start();
    $this->slir->processRequestFromURL();
    $output = ob_get_clean();

    $this->assertHeaderNotSent('HTTP/1.1 304 Not Modified');
    $this->assertTrue($this->slir->isRequestCached());
    $this->assertTrue($this->slir->isRenderedCached());

    $this->assertNotSame($defaultQualityImage, $output);
    $this->assertLessThan(strlen($defaultQualityImage), strlen($output));

    $image = imagecreatefromstring($output);
    $this->assertInternalType('resource', $image);
    $this->assertSame(50, imagesx($image));

    imagedestroy($image);
    unset($image);

    return $output;
  }

}
