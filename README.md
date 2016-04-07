<p align="center">
<img src="http://i.imgur.com/76sJqop.png">
</p>

<p align="center">
<a href="https://packagist.org/packages/junty/junty"><img src="https://img.shields.io/packagist/v/junty/junty.svg?style=flat-square" title="Packagist" alt="Packagist"></a> 
<a href="https://travis-ci.org/the-junty/junty"><img src="https://img.shields.io/travis/the-junty/junty.svg?style=flat-square" title="Travis" alt="Travis"></a> 
<a href="https://scrutinizer-ci.com/g/the-junty/junty/?branch=master"><img src="https://img.shields.io/scrutinizer/g/the-junty/junty.svg?style=flat-square" title="Scrutinizer" alt="Scrutinizer"></a> 
<a href="https://github.com/the-junty/junty/blob/master/LICENSE.md"><img src="https://img.shields.io/github/license/the-junty/junty.svg?style=flat-square" title="GitHub license" alt="GitHub license"></a>
</p>

<p align="center">
Streams handling with tasks for <b>PHP 7</b>. Inspired by <a href="http://gulpjs.com"><img src="https://raw.githubusercontent.com/gulpjs/artwork/master/gulp-2x.png" height="30px" align="center" title="Gulp" alt="Gulp"></a>
</p>

## Documentation
* [Installing](https://github.com/the-junty/junty-docs/blob/master/docs/Installing.md)
* [Usage](https://github.com/the-junty/junty-docs/blob/master/docs/Usage.md)
* [Plugins](https://github.com/the-junty/junty-docs/blob/master/docs/Plugins.md)

## Example
```php
<?php
require 'vendor/autoload.php';

use Junty\Runner\JuntyRunner;
use Gabrieljmj\JuntyMinify\{Css as CssMinifier, Js as JsMinifier}; // Package: gabrieljmj/junty-minify

$junty = new JuntyRunner();

$junty->group('minify', function () {
    $junty->task('css', function () {
        $this->src('./public/css/*.css')
            ->forStreams(new CssMinifier())
            ->forStreams($this->toDir('./public/dist/css')); 
    });

    $junty->task('js', function () {
        $this->src('./public/js/*.js')
            ->forStreams(new JsMinifier())
            ->forStreams($this->toDir('./public/dist/js')); 
    });
});

return $junty;
```

## Creator
| ![@gabrieljmj](https://avatars0.githubusercontent.com/u/2223216?v=3&s=100) |
| ---      |
| [@gabrieljmj](https://github.com/gabrieljmj) |

## License
**Junty** is under [MIT License](https://github.com/the-junty/junty/blob/master/LICENSE.md).