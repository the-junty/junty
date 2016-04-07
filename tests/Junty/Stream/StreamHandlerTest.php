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

/**
 * @coversDefaultClass \Junty\Stream\StreamHandler
 */
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

    /**
     * @covers ::src
     * @covers ::forStreams
     */
    public function testListingAllFilesFromSinglePattern()
    {
        $tasker = new StreamHandler();
        $streams = [];

        $tasker->src(__DIR__ . '/test_files/*.php')
                ->forStream(function ($stream) use (&$streams) {
                    $streams = $stream->getMetaData('uri');
                })
            ->end();

        $this->assertContains(__DIR__ . '/test_files/file.php', $streams);
    }

    /**
     * @covers ::src
     */
    public function testExcludingGlobsByPattern()
    {
        $sh = new StreamHandler();
        $streams = [];

        $sh->src(__DIR__ . '/test_files/*', '/file.txt/')
            ->forStream(function ($stream) use (&$streams) {
                $streams[] = $stream->getMetaData('uri');
            });

        $this->assertContains(__DIR__ . '/test_files/file.php', $streams);
        $this->assertContains(__DIR__ . '/test_files/file.js', $streams);
        $this->assertNotContains(__DIR__ . '/test_files/file.txt', $streams);
    }

    public function testExcludingGlobsByArrayOfPatterns()
    {
        $sh = new StreamHandler();
        $streams = [];

        $sh->src(__DIR__ . '/test_files/*', ['/file.txt/', '/file.php/'])
            ->forStream(function ($stream) use (&$streams) {
                $streams[] = $stream->getMetaData('uri');
            });

        $this->assertNotContains(__DIR__ . '/test_files/file.php', $streams);
        $this->assertNotContains(__DIR__ . '/test_files/file.txt', $streams);
        $this->assertContains(__DIR__ . '/test_files/file.js', $streams);
    }

    /**
     * @covers ::src
     * @covers ::forStreams
     */
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

    /**
     * @covers ::src
     * @covers ::forStream
     */
    public function testSingleStreamHandlerReturnsInstanceOfStream()
    {
        $isStream = true;
        $tasker = new StreamHandler();
        $tasker->src(__DIR__ . '/test_files/*')
                ->forStream(function ($stream) use (&$isStream) {
                    if ($isStream) {
                        $isStream = $stream instanceof StreamInterface;
                    }
                })
            ->end();

        $this->assertTrue($isStream);
    }

    /**
     * @covers ::src
     * @covers ::toDir
     * @covers ::end
     */
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

    /**
     * @covers ::temp
     * @covers ::end
     */
    public function testTemporaryFileIsDeleteOnExecutionEnd()
    {
        $sh = new StreamHandler();

        if (is_dir($dir = __DIR__ . '/temp_dir')) {
            $this->rmdir($dir);
        }

        mkdir($dir);

        file_put_contents($tempfileName = __DIR__ . '/temp_dir/tempfile.txt', '');
        file_put_contents($nottempfileName = __DIR__ . '/temp_dir/nottemp.txt', '');

        $sh->src(__DIR__ . '/temp_dir/*')
            ->forStream(function ($stream) use ($tempfileName) {
                if ($stream->getMetaData('uri') === $tempfileName) {
                    $this->temp($stream);
                }
            })
            ->end();

        $this->assertFalse(file_exists($tempfileName));
        $this->assertTrue(file_exists($nottempfileName));

        $this->rmdir(__DIR__ . '/temp_dir');
    }
}