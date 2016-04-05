Junty
=========
[![Packagist](https://img.shields.io/packagist/v/junty/junty.svg?style=flat-square)](https://packagist.org/packages/junty/junty) [![Travis](https://img.shields.io/travis/the-junty/junty.svg?style=flat-square)](https://travis-ci.org/the-junty/junty) [![Scrutinizer](https://img.shields.io/scrutinizer/g/the-junty/junty.svg?style=flat-square)](https://scrutinizer-ci.com/g/the-junty/junty/?branch=master) [![GitHub license](https://img.shields.io/github/license/the-junty/junty.svg?style=flat-square)](https://github.com/the-junty/junty/blob/master/LICENSE.md)

Streams handling with tasks for **PHP 7**. Inspired by [<img src="https://raw.githubusercontent.com/gulpjs/artwork/master/gulp-2x.png" height="30px" align="center" title="Gulp" alt="Gulp">](http://gulpjs.com)

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