<?php
/**
 * Junty
 *
 * @author Gabriel Jacinto aka. GabrielJMJ <gamjj74@hotmail.com>
 * @license MIT License
 */
 
namespace Test\Junty\Stream;

use Junty\Stream\Stream;

/**
 * @coversDefaultClass \Junty\Stream\Stream
 */
class StreamTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $dir = __DIR__ . '/test_files_for_stream';
        $files = [
            'file.php' => '<?php'
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
        $this->rmdir(__DIR__ . '/test_files_for_stream');
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
     * @covers ::getResource
     */
    public function testGetterForResource()
    {
        $resource = fopen('php://temp', 'r');
        $stream = new Stream($resource);

        $this->assertEquals($stream->getResource(), $resource);

        $stream->close();
    }

    /**
     * @covers ::save
     */
    public function testIfSaveMethodWorksProperly()
    {
        $newContents = 'heeey!';
        $resource = fopen('php://temp', 'r+');
        $stream = new Stream($resource);
        $stream->setContents($newContents);
        $stream->save();

        $this->assertEquals($stream->getContents(), $newContents);
    }

    /**
     * @covers ::pipe
     */
    public function testPipeingResourceStreams()
    {
        $contents = '<?php';
        $resource = fopen(__DIR__ . '/test_files_for_stream/file.php', 'r+');
        $stream = new Stream($resource);
        $dest = fopen(__DIR__ . '/test_files_for_stream/file.txt', 'w+');
        $stream->pipe($dest);
        fclose($dest);

        $destResult = new Stream(fopen(__DIR__ . '/test_files_for_stream/file.txt', 'r'));

        $this->assertEquals($destResult->getContents(), $contents);
    }

    /**
     * @covers ::pipe
     */
    public function testPipeingInstanceOfStreams()
    {
        $contents = '<?php';
        $resource = fopen(__DIR__ . '/test_files_for_stream/file.php', 'r+');
        $stream = new Stream($resource);
        $dest = new Stream(fopen(__DIR__ . '/test_files_for_stream/file_2.txt', 'w+'));
        $stream->pipe($dest);
        $dest->close();

        $destResult = new Stream(fopen(__DIR__ . '/test_files_for_stream/file_2.txt', 'r'));

        $this->assertEquals($destResult->getContents(), $contents);
    }
}