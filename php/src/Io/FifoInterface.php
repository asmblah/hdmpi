<?php

/**
 * KVM & HDMI capture with TypeScript and PHP using Uniter.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/hdmpi
 *
 * Released under the MIT license
 * https://github.com/asmblah/hdmpi/raw/master/MIT-LICENSE.txt
 */

namespace Asmblah\Hdmpi\Io;

use Asmblah\Hdmpi\Frame;

/**
 * Interface FifoInterface.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface FifoInterface
{
    /**
     * Adds a listener for received chunks.
     *
     * @param callable $callback
     */
    public function onChunk(callable $callback);

    /**
     * Pauses receiving data for the stream.
     */
    public function pause();

    /**
     * Opens a WritableStream to the FIFO.
     */
    public function reopenStream();

    /**
     * Resumes receiving data for the stream.
     */
    public function resume();

    /**
     * Writes the given chunk to the FIFO.
     *
     * @param object $chunk
     */
    public function writeChunk($chunk);

    /**
     * Writes the given frame to the FIFO.
     *
     * @param Frame $frame
     */
    public function writeFrame(Frame $frame);
}
