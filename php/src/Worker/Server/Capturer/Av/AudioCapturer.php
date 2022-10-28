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

use Asmblah\Hdmpi\Io\FifoInterface;
use Asmblah\Hdmpi\Logger\LoggerInterface;
use Asmblah\Hdmpi\Session\SessionInterface;
use Asmblah\Hdmpi\Worker\WorkerInterface;

/**
 * Class AudioCapturer.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class AudioCapturer implements WorkerInterface
{
    /**
     * @var object
     */
    private $bufferClass;
    /**
     * @var FifoInterface
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
     * @param FifoInterface $fifo
     * @param object $bufferClass
     */
    public function __construct(
        LoggerInterface $logger,
        SessionInterface $session,
        FifoInterface $fifo,
        $bufferClass
    ) {
        $this->bufferClass = $bufferClass;
        $this->fifo = $fifo;
        $this->logger = $logger;
        $this->session = $session;
    }

    public function start()
    {
        $this->logger->debug('Starting server audio capture');

        $audioBuffer = $this->bufferClass->alloc(992, 0);

        $this->session->capture(function ($message) use ($audioBuffer) {
            $message->copy($audioBuffer, 0, 16);

            $this->fifo->writeChunk($this->bufferClass->from($audioBuffer));
        });

        $this->session->start();
    }
}
