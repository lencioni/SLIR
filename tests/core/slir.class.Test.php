<?php
require_once realpath(__DIR__ . '/../slirTestCase.class.php');

class SLIRTest extends SLIRTestCase
{
  /**
   * @param string $header
   * @return void
   */
  private function assertSentHeader($header)
  {
    $this->assertTrue(in_array($header, $this->slir->getHeaders()));
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

    SLIR::escapeOutputBuffering();

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
   * @outputBuffering enabled
   *
   * @return string image output
   */
  public function processUncachedRequestFromURLWithOnlyWidthSpecified()
  {
    $_SERVER['REQUEST_URI'] = '/slir/w50/slir/tests/images/camera-logo.png';

    $this->slir->uncache();

    $this->assertFalse($this->slir->isRequestCached());
    $this->assertFalse($this->slir->isRenderedCached());

    $this->slir->processRequestFromURL();

    $this->assertTrue($this->slir->isRequestCached());
    $this->assertTrue($this->slir->isRenderedCached());

    $output = ob_get_contents();

    $image = imagecreatefromstring($output);
    $this->assertInternalType('resource', $image);
    $this->assertSame(50, imagesx($image));

    return $output;
  }

  /**
   * @test
   * @outputBuffering enabled
   * @depends processUncachedRequestFromURLWithOnlyWidthSpecified
   *
   * @param string $uncachedImageOutput
   */
  public function processRequestThatShouldBeServedFromTheRequestCache($uncachedImageOutput)
  {
    $_SERVER['REQUEST_URI'] = '/slir/w50/slir/tests/images/camera-logo.png';

    $this->assertTrue($this->slir->isRequestCached());
    $this->assertTrue($this->slir->isRenderedCached());

    $this->slir->processRequestFromURL();

    $this->assertSame($uncachedImageOutput, ob_get_contents());
  }

  /**
   * @test
   * @outputBuffering enabled
   * @depends processUncachedRequestFromURLWithOnlyWidthSpecified
   *
   * @param string $uncachedImageOutput
   */
  public function processRequestThatShouldBeServedFromTheRenderedCache($uncachedImageOutput)
  {

    $_SERVER['REQUEST_URI'] = '/slir/w50-h10000/slir/tests/images/camera-logo.png';

    $this->slir->uncacheRequest();

    $this->assertFalse($this->slir->isRequestCached());
    $this->assertTrue($this->slir->isRenderedCached());

    $this->slir->processRequestFromURL();

    $this->assertTrue($this->slir->isRequestCached());

    $this->assertSame($uncachedImageOutput, ob_get_contents());
  }

  /**
   * @test
   * @outputBuffering enabled
   */
  public function processRequestThatShouldServeSourceImage()
  {
    $_SERVER['REQUEST_URI'] = '/slir/w99999-q100/slir/tests/images/camera-logo.png';

    $this->assertFalse($this->slir->isRequestCached());
    $this->assertFalse($this->slir->isRenderedCached());

    $this->slir->processRequestFromURL();

    $this->assertSame(file_get_contents(realpath(__DIR__ . '/../images/camera-logo.png')), ob_get_contents());

    $this->assertFalse($this->slir->isRequestCached());
    $this->assertFalse($this->slir->isRenderedCached());
  }

  /**
   * @test
   * @outputBuffering enabled
   */
  public function processRequestThatShouldBeCachedInTheBrowser()
  {
    $_SERVER['HTTP_IF_MODIFIED_SINCE'] = gmdate('D, d M Y H:i:s', time() + 100) . ' GMT';

    $_SERVER['REQUEST_URI'] = '/slir/w50/slir/tests/images/camera-logo.png';

    $this->slir->processRequestFromURL();

    $this->assertSentHeader('HTTP/1.1 304 Not Modified');
    $this->assertSame('', ob_get_contents());

    unset($_SERVER['HTTP_IF_MODIFIED_SINCE']);
  }
}
