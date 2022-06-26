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
     */
    public function __construct(LoggerInterface $logger, SessionInterface $session, Fifo $fifo)
    {
        $this->fifo = $fifo;
        $this->logger = $logger;
        $this->session = $session;
    }

    public function capture()
    {
        /** @var Frame|null $currentFrame */
        $currentFrame = null;
        /** @var Frame|null $previousFrame */
        $previousFrame = null;

        $this->logger->debug('Starting video capture');

        $this->session->capture(function ($message, $remoteInfo) use (&$currentFrame, &$previousFrame) {
            $frameNumber = $message->readUint16BE(0);
            $currentFrameChunk = $message->readUint16BE(2);
            $isLastChunk = false;

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

                $currentFrame = new Frame($frameNumber);
            }

            if ($currentFrameChunk > 32768) {
                // MSB set to 1, so this is the last chunk.
                $isLastChunk = true;

                // Strip MSB of 1.
                $currentFrameChunk -= 32768;
            }

            $chunkDelta = $currentFrameChunk - $currentFrame->getChunkNumber();

            if ($chunkDelta > 0) {
                // Missed a chunk of the frame - drop the frame,
                // we don't want to output a partial frame.
                $previousFrame = $currentFrame;
                $currentFrame = null;
                return;
            }

            if ($previousFrame && $frameNumber > $previousFrame->getNumber() + 1) {
                // Missed part of or an entire frame.
                $previousFrame = $currentFrame;
                $currentFrame = null;
                return;
            }

            // Add the chunk to the current frame.
            $currentFrame->addChunk($message->subarray(4));

            if ($isLastChunk) {
                // We've captured an entire MJPEG frame.
                $this->fifo->writeFrame($currentFrame);

                // Use this frame as the new previous one.
                $previousFrame = $currentFrame;

                $currentFrame = null;
            }
        });
    }
}
