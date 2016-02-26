<?php
/**
 * Junty
 *
 * @author Gabriel Jacinto aka. GabrielJMJ <gamjj74@hotmail.com>
 * @license MIT License
 */
 
namespace Test\Junty\Stream;

use Junty\Stream\StreamHandler;
use Psr\Http\Message\StreamInterface;

class StreamHandlerTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $dir = __DIR__ . '/test_files';
        $files = [
            'file.php' => '<?php',
            'file.txt' => 'hello world',
            'file.js' => "'use strict';"
        ];

        /*if (is_dir($dir)) {
            rmdir($dir);
        }*/

        if (!is_dir($dir)) {
            mkdir($dir);
        }

        foreach ($files as $file => $contents) {
            file_put_contents($dir . '/' . $file, $contents);
        }
    }

    protected function tearDown()
    {
        $this->rmdir(__DIR__ . '/test_files');
    }

    private function rmdir($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!$this->rmdir($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }

    public function testListingAllFilesFromPattern()
    {
        $tasker = new StreamHandler();
        $tasker->src(__DIR__ . '/test_files/*.php')
                ->forStreams(function ($streams) {
                    $_SERVER['STREAMS'] = array_map(function ($stream) {
                        return $stream->getMetaData('uri');
                    }, $streams);
                })
            ->end();

        $this->assertContains(__DIR__ . '/test_files/file.php', $_SERVER['STREAMS']);
    }

    public function testStreamsHandlerReturnsArrayWithStreamInstance()
    {
        $allStream = true;
        $tasker = new StreamHandler();
        $tasker->src(__DIR__ . '/test_files/*')
                ->forStreams(function ($streams) use (&$allStream) {
                    foreach ($streams as $stream) {
                        if (!$stream instanceof StreamInterface) {
                            $allStream = false;
                        }
                    }
                })
            ->end();

        $this->assertTrue($allStream);
    }

    public function testSingleStreamHandlerReturnsInstanceOfStream()
    {
        $isStream = true;
        $tasker = new StreamHandler();
        $tasker->src('./test_files/*')
                ->forStream(function ($stream) use (&$isStream) {
                    if ($isStream) {
                        $isStream = $stream instanceof StreamInterface;
                    }
                })
            ->end();

        $this->assertTrue($isStream);
    }

    public function testPushingStream()
    {
        if (is_dir($dir = __DIR__ . '/test_files/pushing_test')) {
            $this->rmdir($dir);
        }

        mkdir($dir);
        $_streams = [];

        $sh = new StreamHandler();
        $sh->src(__DIR__ . '/test_files/*')
                ->forStream(function ($stream) {

                    if ($stream->getContents() === 'hello world') {
                        $this->push($stream);
                    }
                })
                ->forStreams($sh->toDir(__DIR__ . '/test_files/pushing_test'))
            ->end()
            ->src(__DIR__ . '/test_files/pushing_test/*')
                ->forStreams(function ($streams) use (&$_streams) {
                    $_streams = array_map(function ($stream) {
                        return $stream->getMetaData('uri');
                    }, $streams);
                })
            ->end();

        $file = __DIR__ . '/test_files/pushing_test/file.txt';

        $this->assertContains($file, $_streams);
        $this->assertTrue(file_exists($file));
    }
}