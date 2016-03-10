<?php
/**
 * Junty
 *
 * @author Gabriel Jacinto aka. GabrielJMJ <gamjj74@hotmail.com>
 * @license MIT License
 */
 
namespace Junty\Stream;

use GuzzleHttp\Psr7\Stream as GuzzleStream;
use Psr\Http\Message\StreamInterface;

class Stream extends GuzzleStream
{
    private $contents;

    private $resource;

    public function __construct($stream, $options = [])
    {
        parent::__construct($stream, $options);

        $this->resource = $stream;
    }

    /**
     * Returns the stream resource
     *
     * @return resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Sets stream contents
     *
     * @param string $contents
     */
    public function setContents($contents)
    {
        $this->contents = $contents;
    }

    /**
     * Updates the stream contents
     */
    public function save()
    {
        $stream = new self(fopen($this->getMetaData('uri'), 'w'));
        $stream->write($this->contents);
        $stream->close();
    }

    /**
     * If the contents is setted, return it
     *
     * @return string
     */
    public function getContents()
    {
        if (null !== $this->contents) {
            return $this->contents;
        }

        return parent::getContents();
    }

    public function __toString()
    {
        if (null !== $this->contents) {
            return $this->contents;
        }

        return parent::__toString();
    }

    /**
     * Copies a stream to another
     *
     * @param self|resource $stream
     *
     * @return integer
     */
    public function pipe($stream)
    {
        if (!(is_resource($stream) && get_resource_type($stream) === 'stream') && !$stream instanceof StreamInterface) {
            throw new \Exception('Invalid stream type passed. You must pass an instance of \'Junty\Stream\Stream\' or a PHP stream resouce.');
        }

        $stream = $stream instanceof StreamInterface ? $stream->getResource() : $stream;

        return stream_copy_to_stream($this->getResource(), $stream);
    }
}