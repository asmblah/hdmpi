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
use Asmblah\Hdmpi\Logger\LoggerInterface;
use Asmblah\Hdmpi\Session\SessionInterface;

/**
 * Class AudioCapturer.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class AudioCapturer implements CapturerInterface
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
        $this->logger->debug('Starting audio capture');

        $audioBuffer = $this->bufferClass->alloc(992, 0);

        $this->session->capture(function ($message) use ($audioBuffer) {
            $message->copy($audioBuffer, 0, 16);

            $this->fifo->writeChunk($audioBuffer);
        });
    }
}
