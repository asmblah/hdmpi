<?php

/**
 * KVM & HDMI capture with TypeScript and PHP using Uniter.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/hdmpi
 *
 * Released under the MIT license
 * https://github.com/asmblah/hdmpi/raw/master/MIT-LICENSE.txt
 */

namespace Asmblah\Hdmpi\Worker\Client\Forwarder\Av;

use Asmblah\Hdmpi\Io\FifoInterface;
use Asmblah\Hdmpi\Logger\LoggerInterface;
use Asmblah\Hdmpi\Session\MulticastSessionInterface;
use Asmblah\Hdmpi\Tools\BufferTools;
use Asmblah\Hdmpi\Worker\WorkerInterface;
use RuntimeException;

/**
 * Class VideoForwarder.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class VideoForwarder implements WorkerInterface
{
    const FRAME_CHUNK_SIZE = 1020;
    /**
     * @var object
     */
    private $bufferClass;
    /**
     * @var BufferTools
     */
    private $bufferTools;
    /**
     * @var bool
     */
    private $busy;
    /**
     * @var FifoInterface
     */
    private $fifo;
    /**
     * @var int
     */
    private $frameNumber = 0;
    /**
     * @var object
     */
    private $imageEnd;
    /**
     * @var object
     */
    private $imageStart;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var object
     */
    private $receiveBuffer;
    /**
     * @var MulticastSessionInterface
     */
    private $videoSession;
    /**
     * @var MulticastSessionInterface
     */
    private $videoSyncSession;

    /**
     * @param LoggerInterface $logger
     * @param BufferTools $bufferTools
     * @param MulticastSessionInterface $videoSession
     * @param MulticastSessionInterface $videoSyncSession
     * @param FifoInterface $fifo
     * @param object $bufferClass
     */
    public function __construct(
        LoggerInterface $logger,
        BufferTools $bufferTools,
        MulticastSessionInterface $videoSession,
        MulticastSessionInterface $videoSyncSession,
        FifoInterface $fifo,
        $bufferClass
    ) {
        $this->bufferClass = $bufferClass;
        $this->bufferTools = $bufferTools;
        $this->fifo = $fifo;
        $this->logger = $logger;
        $this->videoSession = $videoSession;
        $this->videoSyncSession = $videoSyncSession;

        $this->imageStart = $bufferClass->from([0xff, 0xd8]);
        $this->imageEnd = $bufferClass->from([0xff, 0xd9]);
    }

    /**
     * @inheritDoc
     */
    public function start()
    {
        $this->logger->debug('Starting client video forwarding');

        $this->logger->debug('Binding client video port');
        $this->videoSession->capture(function ($message) {
            // We're only expecting to send multicast to the receiver, not receive anything.
            $this->logger->warn('Unexpected multicast received for video session: ' . $message->toString());
        });
        $this->videoSession->start();

        $this->logger->debug('Binding client video sync port');
        $this->videoSyncSession->capture(function ($message) {
            // We're only expecting to send multicast to the receiver, not receive anything.
            $this->logger->warn('Unexpected multicast received for video sync session: ' . $message->toString());
        });
        $this->videoSyncSession->start();

        $this->receiveBuffer = $this->bufferClass->alloc(0);

        $this->fifo->onChunk([$this, 'consumeChunk']);
    }

    /**
     * Consumes a chunk from the FIFO.
     * Once an entire MJPEG JPEG frame has been received,
     * it is handed off to ->consumeMjpegFrame(...).
     *
     * @param object $chunk
     */
    public function consumeChunk($chunk)
    {
        if ($this->busy) {
            throw new RuntimeException('Already busy!');
        }

        $this->receiveBuffer = $this->bufferClass->concat([$this->receiveBuffer, $chunk]);

        $start = $this->receiveBuffer->indexOf($this->imageStart);
        $end = $this->receiveBuffer->indexOf($this->imageEnd);

        if ($start > -1 && $end > -1) {
            $frameBuffer = $this->receiveBuffer->slice($start, $end + 2);

            $this->consumeMjpegFrame($frameBuffer);

            // Strip off the frame and any leading data.
            $this->receiveBuffer = $this->receiveBuffer->slice($end + 2);
        }
    }

    /**
     * Consumes an entire MJPEG JPEG frame.
     *
     * @param object $mjpegFrame
     */
    private function consumeMjpegFrame($mjpegFrame)
    {
        $this->fifo->pause();
        $this->busy = true;

        $frameSizeBytes = $mjpegFrame->byteLength;
        $chunkNumber = 0;
        $chunkOffset = 0;
        $bufferLengthCheckTotal = 0;

        $this->logger->debug('VideoForwarder :: Received frame');

        // Send the video sync datagram for the frame.
        $this->videoSyncSession->send(
            $this->bufferClass->concat([
                $this->bufferClass->from([0x00, 0x00, 0x00, 0x00]),
                $this->bufferTools->numberTo16BitBigEndian($this->frameNumber),
                $this->bufferClass->from([
                    0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00,
                    0x00, 0x00, 0x00, 0x00, 0x00, 0x00,
                ]),
            ])
        );

        $this->logger->debug('Sent sync datagram for frame #' . $this->frameNumber);

        while ($chunkOffset < $frameSizeBytes) {
            $isLastChunk = $chunkOffset + self::FRAME_CHUNK_SIZE >= $frameSizeBytes;
            $chunk = $mjpegFrame->slice(
                $chunkOffset,
                $chunkOffset + self::FRAME_CHUNK_SIZE
            );
            $frameNumberChunk = $this->bufferTools->numberTo16BitBigEndian($this->frameNumber);
            // Invert MSB of final chunk for the frame.
            $chunkNumberChunk = $this->bufferTools->numberTo16BitBigEndian(
                $isLastChunk ? $chunkNumber | 0x8000 : $chunkNumber
            );

            $datagram = $this->bufferClass->alloc(1024);
            $this->bufferClass->concat([$frameNumberChunk, $chunkNumberChunk, $chunk])
                ->copy($datagram);

            $bufferLengthCheckTotal += $chunk->byteLength;

            $this->videoSession->send($datagram);

            $this->logger->debug("Sent: frame #$this->frameNumber, chunk #$chunkNumber");

            $chunkOffset += self::FRAME_CHUNK_SIZE;
            $chunkNumber++;
        }

        if ($bufferLengthCheckTotal !== $frameSizeBytes) {
            throw new RuntimeException(
                "Length mismatch, should be $frameSizeBytes but we sent $bufferLengthCheckTotal"
            );
        }

        // Frame numbers are 16-bit and so wrap around at 65536.
        $this->frameNumber = ($this->frameNumber + 1) & 0xffff;

        $this->busy = false;
        $this->fifo->resume();
    }
}
