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
use JuntyToDir\ToDirPlugin;

class StreamHandler
{
    private $globs = [];

    private $toPush = [];

    /**
     * Provides streams by the pattern passed
     *
     * @param string|array $accept
     *
     * @return self
     */
    public function src($accept) : self
    {
        if (!is_string($accept) && !is_array($accept)) {
            throw new InvalidArgumentException('You can only pass a string pattern or array with patterns');
        }

        if (is_array($accept)) {
            $fileGroups = [];

            foreach ($accept as $pattern) {
                $fileGroups[] = $this->recoursiveGlob($pattern, GLOB_ERR);
            }

            $this->globs = call_user_func_array('array_merge', $fileGroups);
        } else {
            $this->globs = $this->recoursiveGlob($accept, GLOB_ERR);
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
        $cb = \Closure::bind($this->getPipeCallback($callback), $this);

        if (count($this->toPush)) {
            foreach ($this->toPush as $stream) {
                $cb($stream);
            }

            return $this;
        }

        $streams = array_map(function ($file) {
            return new Stream(fopen($file, 'r+'));
        }, $this->globs);

        foreach ($streams as $stream) {
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
        $cb = \Closure::bind($this->getPipeCallback($callback), $this);

        if (count($this->toPush)) {
            $cb($this->toPush);

            return $this;
        }

        $streams = array_map(function ($file) {
            return new Stream(fopen($file, 'r+'));
        }, $this->globs);

        $cb($streams);

        return $this;
    }

    private function getPipeCallback($cb) : callable
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
     */
    public function push(StreamInterface $stream)
    {
        $this->toPush[] = $stream;
    }

    /**
     * Sends pushed streams to a directory (plugin)
     *
     * @param string $dest
     *
     * @return callable
     */
    public function toDir(string $dest)
    {
        return new ToDirPlugin($dest);
    }

    /**
     * Cleans up pushed streams
     *
     * @return self
     */
    public function end() : self
    {
        foreach ($this->toPush as $stream) {
            $stream->close();
        }

        $this->toPush = [];
        return $this;
    }

    private function recoursiveGlob($pattern, $flags = 0)
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

        foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
            $globs = array_merge($globs, $this->recoursiveGlob($dir.'/'.basename($pattern), $flags));
        }

        $globs = array_filter($globs, function ($res) {
            return !is_dir($res);
        });

        return $globs;
    }
}