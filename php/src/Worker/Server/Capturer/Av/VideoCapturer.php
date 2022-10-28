<?php

/**
 * KVM & HDMI capture with TypeScript and PHP using Uniter.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/hdmpi
 *
 * Released under the MIT license
 * https://github.com/asmblah/hdmpi/raw/master/MIT-LICENSE.txt
 */

namespace Asmblah\Hdmpi\Worker\Server\Capturer\Av;

use Asmblah\Hdmpi\Frame;
use Asmblah\Hdmpi\Io\FifoInterface;
use Asmblah\Hdmpi\Logger\LoggerInterface;
use Asmblah\Hdmpi\Session\MulticastSessionInterface;
use Asmblah\Hdmpi\Worker\WorkerInterface;

/**
 * Class VideoCapturer.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class VideoCapturer implements WorkerInterface
{
    /**
     * @var FifoInterface
     */
    private $fifo;
    /**
     * @var LoggerInterface
     */
    private $logger;
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
     * @param MulticastSessionInterface $videoSession
     * @param MulticastSessionInterface $videoSyncSession
     * @param FifoInterface $fifo
     */
    public function __construct(
        LoggerInterface $logger,
        MulticastSessionInterface $videoSession,
        MulticastSessionInterface $videoSyncSession,
        FifoInterface $fifo
    ) {
        $this->fifo = $fifo;
        $this->logger = $logger;
        $this->videoSession = $videoSession;
        $this->videoSyncSession = $videoSyncSession;
    }

    public function start()
    {
        /** @var Frame|null $currentFrame */
        $currentFrame = null;

        $this->logger->info('Starting server video capture');

        $this->videoSession->capture(function ($message, $remoteInfo) use (
            &$currentFrame
        ) {
            $chunkFrameNumber = $message->readUint16BE(0);
            $currentFrameChunk = $message->readUint16BE(2);
            $isLastChunk = false;

            if (!$currentFrame) {
                $this->logger->warn(
                    'Skipping chunk #' . $currentFrameChunk . ' of frame ' .
                    '#' . $chunkFrameNumber . ', no current frame'
                );

                return;
            }

            if ($chunkFrameNumber > $currentFrame->getNumber()) {
                $this->logger->warn(
                    'Skipping early chunk #' . $currentFrameChunk . ' of frame #' .
                    $chunkFrameNumber . ', as current frame is #' . $currentFrame->getNumber()
                );

                return;
            }

            if ($chunkFrameNumber < $currentFrame->getNumber()) {
                $this->logger->warn(
                    'Skipping late chunk #' . $currentFrameChunk . ' of frame #' .
                    $chunkFrameNumber . ', as current frame is #' . $currentFrame->getNumber()
                );

                return;
            }

            if ($currentFrameChunk > 32768) {
                // MSB set to 1, so this is the last chunk.
                $isLastChunk = true;

                // Strip MSB of 1.
                $currentFrameChunk -= 32768;
            }

            if ($currentFrameChunk !== $currentFrame->getChunkNumber()) {
                $this->logger->warn(
                    'Skipping unwanted chunk #' . $currentFrameChunk . ' of frame #' .
                    $chunkFrameNumber .
                    ', need chunk #' . $currentFrame->getChunkNumber()
                );

                return;
            }

            // Add the chunk to the current frame.
            $currentFrame->addChunk($message->subarray(4));

            if ($isLastChunk) {
                // We've captured an entire MJPEG frame.
                $this->fifo->writeFrame($currentFrame);

                $currentFrame = null;
            }
        });
        $this->videoSession->start();

        $this->videoSyncSession->capture(function ($message, $remoteInfo) use (
            &$currentFrame
        ) {
            $frameNumber = $message->readUint16BE(4);

            if ($currentFrame && $currentFrame->getNumber() !== $frameNumber) {
                // An earlier existing frame was being built, discard it.
                $this->logger->warn(
                    'Discarding partial frame #' . $currentFrame->getNumber() .
                    ' in favour of frame #' . $frameNumber
                );
            }

            // Create a new frame for the next frame number.
            $currentFrame = new Frame($frameNumber);
        });

        $this->videoSyncSession->start();
    }
}
