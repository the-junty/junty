<?php
/**
 * Junty
 *
 * @author Gabriel Jacinto aka. GabrielJMJ <gamjj74@hotmail.com>
 * @license MIT License
 */
 
namespace Junty\Stream;

use GuzzleHttp\Psr7\Stream as GuzzleStream;

class Stream extends GuzzleStream
{
    private $contents;

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
}