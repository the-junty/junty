Junty
=========
Run tasks for handle streams. Inspired by [Gulp](gulpjs.com).

## Install
Run
```bash
$ composer require --dev junty/junty
```

## Usage
### Instance
Create a file called ```junty.php``` returning the ```Runner``` instance.
```php
<?php
require 'vendor/autoload.php';

use Junty\Runner;
use Junty\Stream\Stream;

$junty = new Runner();

//... tasks

return $tasker;
```

### Creting a task
Tasks can be create in two ways: ```function``` and a ```class```.

#### ```function```
```php
$junty->task('copy_php_files', function () {
    $this->src('*.php')
        ->forStream(function (Stream $stream) {
            $this->push($stream);
        })
        ->forStreams($this->toDir('php_files'))
});
```

#### ```class```
Class must implements ```Junty\TaskInterface``` or extends ```Junty\AbstractTask```.
```php
use Junty\AbstractTask;

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

### Stream handling methods
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

## Running
```bash
$ vendor/bin/junty run
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