<?php

/**
 * KVM & HDMI capture with TypeScript and PHP using Uniter.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/hdmpi
 *
 * Released under the MIT license
 * https://github.com/asmblah/hdmpi/raw/master/MIT-LICENSE.txt
 */

namespace Asmblah\Hdmpi;

/**
 * Class Frame.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class Frame
{
    /**
     * @var object[]
     */
    private $chunks;
    /**
     * @var bool
     */
    private $dropped = false;
    /**
     * @var int
     */
    private $number;

    /**
     * @param int $number
     * @param object[] $chunks
     */
    public function __construct($number, array $chunks = [])
    {
        $this->chunks = $chunks;
        $this->number = $number;
    }

    /**
     * @param object $chunk
     */
    public function addChunk($chunk)
    {
        $this->chunks[] = $chunk;
    }

    /**
     * Drops this frame.
     */
    public function drop()
    {
        $this->dropped = true;
    }

    /**
     * Fetches the current chunk number.
     *
     * @return int
     */
    public function getChunkNumber()
    {
        return count($this->chunks);
    }

    /**
     * Fetches all chunks of this frame.
     *
     * @return object[]
     */
    public function getChunks()
    {
        return $this->chunks;
    }

    /**
     * Fetches the sequence number of this frame.
     *
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Fetches whether this frame has been dropped.
     *
     * @return bool
     */
    public function isDropped()
    {
        return $this->dropped;
    }

    /**
     * Duplicates this frame with the given number.
     *
     * @param int $number
     * @return Frame
     */
    public function withNumber($number)
    {
        return new Frame($number, $this->chunks);
    }
}
