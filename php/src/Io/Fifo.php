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
use Asmblah\Hdmpi\Logger\LoggerInterface;

/**
 * Class Fifo.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class Fifo implements FifoInterface
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
     * @var callable|null
     */
    private $onChunkCallback;
    /**
     * @var callable
     */
    private $openStream;
    /**
     * @var object
     */
    private $stream;
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
     * @inheritDoc
     */
    public function onChunk(callable $callback)
    {
        $this->onChunkCallback = $callback;
    }

    /**
     * @inheritDoc
     */
    public function pause()
    {
        $this->stream->pause();
    }

    /**
     * @inheritDoc
     */
    public function reopenStream()
    {
        $fifo = $this;
        $logger = $this->logger;
        $this->stream = ($this->openStream)();

        $onChunkCallback =& $this->onChunkCallback;

        $this->stream->on('data', function ($chunk) use (&$onChunkCallback) {
            if ($onChunkCallback) {
                $onChunkCallback($chunk);
            }
        });

        $this->stream->on('error', function ($error) use ($fifo, $logger) {
            $logger->error($error->message);

            // Reopen pipe on "[ERROR] EPIPE: broken pipe, write"
            // (when ffmpeg disconnects for some reason, e.g. player disconnects in tcp/listen mode).
            $fifo->reopenStream();
        });
    }

    /**
     * @inheritDoc
     */
    public function resume()
    {
        $this->stream->resume();
    }

    /**
     * @inheritDoc
     */
    public function writeChunk($chunk)
    {
        ($this->writeToWriteStream)($this->stream, $chunk);
    }

    /**
     * @inheritDoc
     */
    public function writeFrame(Frame $frame)
    {
        $chunks = $frame->getChunks();

        $frameBuffer = $this->bufferClass->concat($chunks);

        ($this->writeToWriteStream)($this->stream, $frameBuffer);
    }
}
