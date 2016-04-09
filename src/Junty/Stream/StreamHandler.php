<?php 
/**
 * Junty
 *
 * @author Gabriel Jacinto aka. GabrielJMJ <gamjj74@hotmail.com>
 * @license MIT License
 */

namespace Junty\Stream;

use Psr\Http\Message\StreamInterface;
use GuzzleHttp\Psr7;
use Junty\Stream\Stream;
use Junty\Plugin\PluginInterface;
use Junty\ToDir\ToDirPlugin;

class StreamHandler
{
    private $globs = [];

    private $toPush = [];

    private $temp = [];

    private $streams = [];

    /**
     * Provides streams by the pattern passed
     *
     * @param string|array      $accept
     * @param string|array|null $exclude
     *
     * @return self
     */
    public function src($accept, $exclude = null) : self
    {
        if ((!is_string($accept) && !is_array($accept)) || ($exclude !== null && !is_string($exclude) && !is_array($exclude))) {
            throw new \InvalidArgumentException('You can only pass a string pattern or array with patterns');
        }

        if (is_array($accept)) {
            $fileGroups = [];

            foreach ($accept as $pattern) {
                $fileGroups[] = $this->recoursiveGlob($pattern, GLOB_ERR);
            }

            $globs = call_user_func_array('array_merge', $fileGroups);
        } else {
            $globs = $this->recoursiveGlob($accept, GLOB_ERR);
        }

        $this->globs = $this->ignoreFiles($globs, $exclude);

        foreach ($this->globs as $glob) {
            $this->streams[] = new Stream(fopen($glob, 'r+'));
        }

        return $this;
    }

    /**
     * Handle each stream
     *
     * @param callable|PluginInterface $callback
     *
     * @return self
     */
    public function forStream($callback) : self
    {
        $cb = \Closure::bind($this->getCallback($callback), $this);

        if (count($this->toPush)) {
            foreach ($this->toPush as &$stream) {
                $cb($stream);
            }

            return $this;
        }

        foreach ($this->streams as &$stream) {
            $cb($stream);
        }

        return $this;
    }

    /**
     * Handle all streams
     *
     * @param callable|PluginInterface $callback
     *
     * @return self
     */
    public function forStreams($callback) : self
    {
        $cb = \Closure::bind($this->getCallback($callback), $this);

        if (count($this->toPush)) {
            $cb($this->toPush);

            return $this;
        }

        $cb($this->streams);

        return $this;
    }

    private function getCallback($cb) : callable
    {
        if (!($cb instanceof PluginInterface) && !is_callable($cb)) {
            throw new \InvalidArgumentException('Invalid callback type: ' + gettype($cb));
        }

        if ($cb instanceof PluginInterface) {
            return $cb->getCallback();
        }

        return $cb;
    }

    /**
     * Pushes a stream to be used on destination
     *
     * @param StreamInterface $stream
     */
    public function push(StreamInterface $stream)
    {
        $this->toPush[] = $stream;
    }

    /**
     * Creates a temporary stream
     * It will be deleted in the end of task execution
     *
     * @param StreamInterface $stream
     */
    public function temp(StreamInterface $stream)
    {
        $this->push($stream);
        $this->temp[] = $stream->getMetadata('uri');
    }

    /**
     * Sends pushed streams to a directory (plugin)
     *
     * @param string $dest
     *
     * @return callable
     */
    public function toDir(string $dest) : ToDirPlugin
    {
        return new ToDirPlugin($dest);
    }

    /**
     * Cleans up pushed streams and delete indicated temporary files
     *
     * @return self
     */
    public function end() : self
    {
        $this->closeAll();

        foreach ($this->temp as $tempf) {
            unlink($tempf);
        }

        $this->toPush = [];
        return $this;
    }

    /**
     * Destructor closes all opened streams
     */
    public function __destruct()
    {
        $this->closeAll();
    }

    private function recoursiveGlob($pattern, $flags = 0) : array
    {
        $globs = glob($pattern, $flags);
        $hasDir = false;

        foreach ($globs as $glob) {
            if (is_dir($glob)) {
                $hasDir = true;
            }
        }

        if (!$hasDir) {
            return $globs;
        }

        foreach (
            glob(
                dirname($pattern) . DIRECTORY_SEPARATOR . '*',
                GLOB_ONLYDIR | GLOB_NOSORT
            )
            as $dir
        ) {
            $globs = array_merge(
                $globs,
                $this->recoursiveGlob(
                    $dir . DIRECTORY_SEPARATOR . basename($pattern), $flags
                )
            );
        }

        $globs = array_filter($globs, function ($res) {
            return !is_dir($res);
        });

        return $globs;
    }

    /**
     * Ignore files provided by glob function
     *
     * @param array             $files
     * @param array|string|null $patterns
     *
     * @return array
     */
    private function ignoreFiles(array $files, $patterns)
    {
        if (null !== $patterns) {
            $cbFilter = function () {return true;};

            if (is_array($patterns)) {
                $cbFilter = function ($glob) use ($patterns) {
                    foreach ($patterns as $pattern) {
                        if (preg_match($pattern, $glob)) {
                            return false;
                        }
                    }

                    return true;
                };
            } elseif (is_string($patterns)) {
                $cbFilter = function ($glob) use ($patterns) {
                    return !preg_match($patterns, $glob);
                };
            }

            $files = array_filter($files, $cbFilter);
        }

        return $files;
    }

    /**
     * Closes all opened streams
     */
    private function closeAll()
    {
        foreach ($this->streams as $stream) {
            $stream->close();
        }

        foreach ($this->toPush as $stream) {
            $stream->close();
        }
    }
}