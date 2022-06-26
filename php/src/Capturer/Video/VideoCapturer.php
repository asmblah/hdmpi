<?php

/**
 * KVM & HDMI capture with TypeScript and PHP using Uniter.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/hdmpi
 *
 * Released under the MIT license
 * https://github.com/asmblah/hdmpi/raw/master/MIT-LICENSE.txt
 */

namespace Asmblah\Hdmpi\Capturer\Video;

use Asmblah\Hdmpi\Capturer\CapturerInterface;
use Asmblah\Hdmpi\Fifo;
use Asmblah\Hdmpi\Frame;
use Asmblah\Hdmpi\Logger\LoggerInterface;
use Asmblah\Hdmpi\Session\SessionInterface;

/**
 * Class VideoCapturer.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class VideoCapturer implements CapturerInterface
{
    /**
     * @var object
     */
    private $bufferClass;
    /**
     * @var Fifo
     */
    private $fifo;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @param LoggerInterface $logger
     * @param SessionInterface $session
     * @param Fifo $fifo
     * @param object $bufferClass
     */
    public function __construct(LoggerInterface $logger, SessionInterface $session, Fifo $fifo, $bufferClass)
    {
        $this->bufferClass = $bufferClass;
        $this->fifo = $fifo;
        $this->logger = $logger;
        $this->session = $session;
    }

    public function capture()
    {
        $logger = $this->logger;
        /** @var Frame|null $currentFrame */
        $currentFrame = null;
        /** @var Frame|null $previousFrame */
        $previousFrame = null;

        $this->logger->debug('Starting video capture');

        $this->session->capture(function ($message, $remoteInfo) use (&$currentFrame, $logger, &$previousFrame) {
//            $this->fifo->writeChunk($this->bufferClass->from($message->subarray(4)));

            $frameNumber = $message->readUint16BE(0);
            $currentFrameChunk = $message->readUint16BE(2);
            $isLastChunk = false;
//            /** @var string|null $droppedFrameReason */
//            $droppedFrameReason = null;

//            $logger->debug(sprintf(
//                'Received packet on port %d, frame #%d, chunk #%d',
//                $remoteInfo['port'],
//                $frameNumber,
//                $currentFrameChunk
//            ));

//            return;

            if ($currentFrame && $frameNumber > $currentFrame->getNumber()) {
                // We missed the end of the previous frame - just discard it now
                // and start on this next (or subsequent) one.
                $currentFrame = null;
            }

            if (!$currentFrame) {
                if ($currentFrameChunk > 0) {
                    // We missed the start of this frame - just discard the rest of it.
                    return;
                }

//                $logger->debug('Creating frame #' . $frameNumber);

                $currentFrame = new Frame($frameNumber);

//                if ($currentFrameChunk > 0) {
//                    // We missed the start of this frame - just discard the rest of it.
////                    $droppedFrameReason = 'Missed frame start';
//                    $currentFrame->drop();
//                    return;
//                }
            }

//            if ($frameNumber % 2 === 0 || $frameNumber % 3 === 0) {
////                $droppedFrameReason = 'Half frame rate test';
//                $previousFrame = $currentFrame;
//                $currentFrame = null;
//                return;
//            }

            if ($currentFrameChunk > 32768) {
                // MSB set to 1, so this is the last chunk.
                $isLastChunk = true;

                // Strip MSB of 1.
                $currentFrameChunk -= 32768;
            }

            $chunkDelta = $currentFrameChunk - $currentFrame->getChunkNumber();

            if (/*$droppedFrameReason === null && */$chunkDelta > 0) {
                // Missed a chunk of the frame - drop the frame,
                // we don't want to output a partial frame.
                // TODO: As this is UDP, support out-of-order?
//                $droppedFrameReason = 'Missed middle chunk of frame';
                $previousFrame = $currentFrame;
                $currentFrame = null;
                return;
            }

            if (/*$droppedFrameReason === null && */$previousFrame && $frameNumber > $previousFrame->getNumber() + 1) {
                // Missed part of or an entire frame.
//                $droppedFrameReason = 'Missed part of or entire frame';
                $previousFrame = $currentFrame;
                $currentFrame = null;
                return;
            }

//            if ($droppedFrameReason !== null) {
//                // We dropped the frame.
//
//                if (!$currentFrame->isDropped()) {
//                    if ($previousFrame) {
//                        // We previously captured a frame, use that in place
//                        // of the one we missed.
////                        $this->fifo->writeFrame($previousFrame);
//
//                        // Use the current frame's number too.
//                        $previousFrame = $previousFrame->withNumber($frameNumber);
//                    }
//
//                    // Discard the current, partial frame.
//                    $currentFrame->drop();
//
//                    $logger->warn('Frame #' . $currentFrame->getNumber() . ' dropped (' . $droppedFrameReason . ')');
//                }
//            } else {
                // Add the chunk to the current frame.
//                $currentFrame->addChunk($this->bufferClass->from($message->subarray(4)));
                $currentFrame->addChunk($message->subarray(4));
//            }

            if ($isLastChunk/* && $droppedFrameReason === null*/) {
                // We've captured an entire MJPEG frame.

//                $logger->debug('Captured entire frame #' . $currentFrame->getNumber());

                $this->fifo->writeFrame($currentFrame);

                // Use this frame as the new previous one.
                $previousFrame = $currentFrame;

                $currentFrame = null;
            }
        });
    }
}
