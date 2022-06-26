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

use Asmblah\Hdmpi\Logger\LoggerInterface;

/**
 * Class Fifo.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class Fifo
{
    /**
     * @var object
     */
    private $bufferClass;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var callable
     */
    private $openStream;
    /**
     * @var object
     */
    private $writeStream;
    /**
     * @var callable
     */
    private $writeToWriteStream;

    /**
     * @param LoggerInterface $logger
     * @param object $bufferClass
     * @param callable $openStream
     * @param callable $writeToWriteStream
     */
    public function __construct(
        LoggerInterface $logger,
        $bufferClass,
        callable $openStream,
        callable $writeToWriteStream
    ) {
        $this->bufferClass = $bufferClass;
        $this->logger = $logger;
        $this->openStream = $openStream;
        $this->writeToWriteStream = $writeToWriteStream;

        $this->reopenStream();
    }

    /**
     * Opens a WritableStream to the FIFO.
     */
    public function reopenStream()
    {
        $fifo = $this;
        $logger = $this->logger;
        $this->writeStream = ($this->openStream)();

        $this->writeStream->on('error', function ($error) use ($fifo, $logger) {
            $logger->error($error->message);

            // Reopen pipe on "[ERROR] EPIPE: broken pipe, write"
            // (when ffmpeg disconnects for some reason, e.g. player disconnects in tcp/listen mode).
            $fifo->reopenStream();
        });
    }

    /**
     * Writes the given chunk to the FIFO.
     *
     * @param object $chunk
     */
    public function writeChunk($chunk)
    {
        ($this->writeToWriteStream)($this->writeStream, $chunk);
    }

    /**
     * Writes the given frame to the FIFO.
     *
     * @param Frame $frame
     */
    public function writeFrame(Frame $frame)
    {
        $chunks = $frame->getChunks();

        $frameBuffer = $this->bufferClass->concat($chunks);

        ($this->writeToWriteStream)($this->writeStream, $frameBuffer);
    }
}
