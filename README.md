# SLIR (Smart Lencioni Image Resizer)

SLIR (Smart Lencioni Image Resizer) resizes images, intelligently sharpens, crops based on width:height ratios, color fills transparent GIFs and PNGs, and caches variations for optimal performance.

For questions or support, please [visit the SLIR Google Group](https://groups.google.com/forum/?fromgroups#!forum/smart-lencioni-image-resizer). If you have found a bug, please [use the issue tracker](https://github.com/lencioni/SLIR/issues).

## Requirements

* [PHP](http://php.net) 5.1.2+
* [GD Graphics Library](http://php.net/manual/en/book.image.php) -- must be a version that supports `imageconvolution()`, such as the bundled version

### Recommended

* [mod_rewrite](http://httpd.apache.org/docs/2.4/mod/mod_rewrite.html)

## Setting up

Download and unpack to a directory in your web root. I recommend putting SLIR in `/slir/` for ease of use. For example, if your website is `http://yourdomain.com`, then SLIR would be at `http://yourdomain.com/slir/`.

After you have SLIR downloaded, visit `http://yourdomain.com/slir/install/` in your favorite web browser.

## Using

To use SLIR, place an `<img\>` tag with the `src` attribute pointing to the path of SLIR (typically "/slir/") followed by the parameters, followed by the path to the source image to resize (e.g. `<img src="/slir/w100/path/to/image.jpg"/>`). All parameters follow the pattern of a one-letter code and then the parameter value:

<table>
  <thead>
    <tr>
      <th>Parameter</th>
      <th>Mearning</th>
      <th>Example</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><var>w</var></td>
      <td>Maximum width</td>
      <td><code>/slir/<strong>w100</strong>/path/to/image.jpg</code></td>
    </tr>
    <tr>
      <td><var>h</var></td>
      <td>Maximum height</td>
      <td><code>/slir/<strong>h100</strong>/path/to/image.jpg</code></td>
    </tr>
    <tr>
      <td><var>c</var></td>
      <td>Crop ratio</td>
      <td><code>/slir/<strong>c1x1</strong>/path/to/image.jpg</code></td>
    </tr>
    <tr>
      <td><var>q</var></td>
      <td>Quality</td>
      <td><code>/slir/<strong>q60</strong>/path/to/image.jpg</code></td>
    </tr>
    <tr>
      <td><var>b</var></td>
      <td>Background fill color</td>
      <td><code>/slir/<strong>bf00</strong>/path/to/image.png</code></td>
    </tr>
    <tr>
      <td><var>p</var></td>
      <td>Progressive</td>
      <td><code>/slir/<strong>p1</strong>/path/to/image.jpg</code></td>
    </tr>
  </tbody>
</table>

Separate multiple parameters with a hyphen: <code>/slir/w100<strong>-</strong>h100<strong>-</strong>c1x1/path/to/image.jpg</code>

### Examples

#### Resizing an image to a max width of 100 pixels and a max height of 100 pixels

    <img src="/slir/w100-h100/path/to/image.jpg"/>

#### Resizing and cropping an image into a square

    <img src="/slir/w100-h100-c1x1/path/to/image.jpg"/>

#### Resizing and cropping an image to exact dimensions

To do this, you simply need to make the crop ratio match up with the desired width and height. For example, if you want your image to be exactly 150 pixels wide by 100 pixels high, you could do this:

    <img src="/slir/w150-h100-c150x100/path/to/image.jpg"/>

Or, more concisely:

    <img src="/slir/w150-h100-c15x10/path/to/image.jpg"/>

However, SLIR will not enlarge images. So, if your source image is smaller than the desired size you will need to use CSS to make it the correct size.

#### Resizing a JPEG without interlacing (for use in Flash)

    <img src="/slir/w100-p0/path/to/image.jpg"/>

#### Matting a PNG with #990000

    <img src="/slir/b900/path/to/image.png"/>

#### Without mod_rewrite (not recommended)

    <img src="/slir/?w=100&amp;h=100&amp;c=1x1&amp;i=/path/to/image.jpg"/>

#### Special characters (e.g. `+`) in image filenames

Filenames that include special characters must be URL-encoded (e.g. plus sign, `+`, should be encoded as `%2B`) in order for SLIR to recognize them properly. This can be accomplished by passing your filenames through PHP's `rawurlencode()` function.

    <img src="/slir/w100/path/to/image%2Bfile.jpg"/>

## Supporting SLIR

If you would like to support SLIR or to show your appreciation for the time spent developing this project, please make a financial contribution.

* [Dwolla](https://www.dwolla.com/hub/lencioni)
* [Flattr](http://flattr.com/thing/178729/Smart-Lencioni-Image-Resizer-SLIR)

***

For more documentation, open `core/slir.class.php` in your favorite text editor.