Junty
=========
[![Packagist](https://img.shields.io/packagist/v/junty/junty.svg?style=flat-square)](https://packagist.org/packages/junty/junty) [![Travis](https://img.shields.io/travis/the-junty/junty.svg?style=flat-square)](https://travis-ci.org/the-junty/junty) [![Scrutinizer](https://img.shields.io/scrutinizer/g/the-junty/junty.svg?style=flat-square)](https://scrutinizer-ci.com/g/the-junty/junty/?branch=master) [![GitHub license](https://img.shields.io/github/license/the-junty/junty.svg?style=flat-square)](https://github.com/the-junty/junty/blob/master/LICENSE.md)

Streams handling with tasks for **PHP 7**. Inspired by [<img src="https://raw.githubusercontent.com/gulpjs/artwork/master/gulp-2x.png" height="30px" align="center" title="Gulp" alt="Gulp">](http://gulpjs.com)

## Install
Run
```bash
$ composer require --dev junty/junty
```

## Usage
### Instance
Create a file called ```juntyfile.php``` returning the ```Runner``` instance.
```php
<?php
require 'vendor/autoload.php';

use Junty\Runner\JuntyRunner;
use Junty\Stream\Stream;

$junty = new JuntyRunner();

//... tasks

return $junty;
```

### Creating tasks and groups
#### Tasks
Tasks can be created in two ways: ```function``` and a ```class```.

##### ```function```
```php
$junty->task('copy_php_files', function () {
    $this->src('*.php')
        ->forStream(function (Stream $stream) {
            $this->push($stream);
        })
        ->forStreams($this->toDir('php_files'));
});
```

##### ```class```
Class must implements ```Junty\TaskRunner\Task\TaskInterface``` or extends ```Junty\TaskRunner\Task\AbstractTask```.
```php
use Junty\TaskRunner\Task\AbstractTask;

$junty->task(new class() extends AbstractTrask
{
    public function getName() : string
    {
        return 'copy_php_files';
    }
    
    public function getCallback() : callable
    {
        return function () {
            $this->src('*.php')
                ->forStream(function (Stream $stream) {
                    $this->push($stream);
                })
                ->forStreams($this->toDir('php_files'));
        };
    }
});
```

#### Groups
Same for groups: ```function``` or ```class```.

##### ```function```
```php
$junty->group('minify', function () {
    $this->task('JS', function () {
        $this->src('./public/js/*.js')
            ->forStreams(new JsMinify())
            ->forStreams($this->toDir('./public/dist/js'));
    }); 

    $this->task('CSS', function () {
        $this->src('./public/css/*.css')
            ->forStreams(new CssMinify())
            ->forStreams($this->toDir('./public/dist/css'));
    }); 
});
```

##### ```class```
```php
use Junty\TaskRunner\Task\{Group, TasksCollection};

$junty->group(new class() extends Group
{
    public function __construct()
    {
    }

    public function task($name, callable $callback = null)
    {
    }

    public function getName() : string
    {
        return 'minify';
    }

    public function getTasks() : TasksCollection
    {
        $collection = TasksCollection();

        $collection->set('JS', function () {
            $this->src('./public/js/*.js')
                ->forStreams(new JsMinify())
                ->forStreams($this->toDir('./public/dist/js'));
        });

        $collection->set('CSS', function () {
            $this->src('./public/css/*.css')
                ->forStreams(new CssMinify())
                ->forStreams($this->toDir('./public/dist/css'));
        });

        return $collection;
    }
});
```

### Stream handling methods
#### About ```Junty\Stream\Stream```
```Junty\Stream\Stream``` implements the [PSR-7 Stream Interface (```Psr\Http\Message\StreamInterface```)](http://www.php-fig.org/psr/psr-7/#3-4-psr-http-message-streaminterface).

#### ```src```
Provides streams by the pattern passed.
```php
$this->src('*.php')
    ->forStreams(function (array $streams) {
        foreach ($streams as $stream) {
            echo $stream->getMetaData('uri');
        }
    });
```

#### ```forStreams```
Provides all streams gotten from ```src```.
```php
$this->src('*.txt')
    ->forStreams(function (array $streams) {
        foreach ($streams as $stream) {
            // Handle each stream
        }
    });
```

#### ```forStream```
Handle each stream gotten from ```src```.
```php
$this->src('*.txt')
    ->forStream(function (Stream $streams) {
       // Handle unique stream
    });
```

#### ```push```
Pushed stream will substitute the list from ```src```.
```php
$this->src('*.txt')
    ->forStreams(function (array $streams) {
        foreach ($streams as $stream) {
            if ($stream->getContents() === 'hello') {
                $this->push($stream);
            }
        }
    })
    ->forStreams(function (array $streams) {
        foreach ($streams as $stream) {
            echo $stream->getContents(); // 'hello' for each stream
        }
    });
```

#### ```end```
Empties list of streams.
```php
$this->src('*.txt')
    ->forStreams(function (array $streams) {
        foreach ($streams as $stream) {
            echo $stream->getContents(); // 'hello' for each stream
        }
    })
    ->end();
    ->src('*.php')
        // ...
```

#### ```toDir```
Put all streammed files on a certain directory.
```php
$this->src('*.php')
    ->forStreams($this->toDir('php_files')); // Copy all files to php_files
```

#### ```Stream::setContents```
Updates the contents of a stream.
```php
->forStream(function (Stream $stream) {
    $stream->setContents('Hello! ;)');
}); // Copy all files to php_files
```

#### ```Stream::save```
Saves the stream contents modifications.
```php
$stream->setContents('Hello! ;)');
$stream->save();
```

#### ```Stream::pipe```
Pipes a stream to another. It's possible to pass an instance of ```Junty\Stream\Stream``` or a PHP Stream Resource.
```php
use Junty\Stream\Stream;

// PHP resource
$stream->pipe(fopen('destination.txt', 'w+'));

// Stream instance
$stream->pipe(new Stream(fopen('destination.txt', 'w+')));
```

### Ordering tasks
You set the execution order of tasks using the method ```JuntyRunner::order```.
```php
// ... tasks

$junty->order('task_2', 'task_1', 'task_4', 'task_3');

return $junty;
```

## Running
```bash
$ vendor/bin/junty
```

## Usage exmaple
### Zipping archives
```php
$junty->task('zip', function () {
    $this->src('dest/*')
        ->forStreams(function (array $streams) {
            $zip = new ZipArchive();

            if ($zip->open('dest.zip', ZipArchive::CREATE)) {
                foreach ($streams as $stream) {
                    $zip->addFromString($stream->getMetaData('uri'), $stream->getContents());
                }

                $zip->close();
            }
        });
});
```
## Creator
| ![@gabrieljmj](https://avatars0.githubusercontent.com/u/2223216?v=3&s=100) |
| ---      |
| [@gabrieljmj](https://github.com/gabrieljmj) |

## License
**Junty** is under [MIT License](https://github.com/the-junty/junty/blob/master/LICENSE.md).