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

/**
 * Class AudioForwarder.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class AudioForwarder implements WorkerInterface
{
    /**
     * @var object
     */
    private $audioBuffer;
    /**
     * @var MulticastSessionInterface
     */
    private $audioSession;
    /**
     * @var object
     */
    private $bufferClass;
    /**
     * @var BufferTools
     */
    private $bufferTools;
    /**
     * @var FifoInterface
     */
    private $fifo;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var object
     */
    private $receiveBuffer;

    /**
     * @param LoggerInterface $logger
     * @param BufferTools $bufferTools
     * @param MulticastSessionInterface $audioSession
     * @param FifoInterface $fifo
     * @param object $bufferClass
     */
    public function __construct(
        LoggerInterface $logger,
        BufferTools $bufferTools,
        MulticastSessionInterface $audioSession,
        FifoInterface $fifo,
        $bufferClass
    ) {
        $this->audioSession = $audioSession;
        $this->bufferClass = $bufferClass;
        $this->bufferTools = $bufferTools;
        $this->fifo = $fifo;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function start()
    {
        $this->logger->debug('Starting client audio forwarding');

        $this->logger->debug('Binding client audio port');
        $this->audioSession->capture(function ($message) {
            // We're only expecting to send multicast to the receiver, not receive anything.
            $this->logger->warn('Unexpected multicast received for audio session: ' . $message->toString());
        });
        $this->audioSession->start();

        $this->receiveBuffer = $this->bufferClass->alloc(0);

        $this->audioBuffer = $this->bufferClass->alloc(1008, 0);

        // Send the standard header for audio datagrams (always the same).
        $this->audioBuffer->set(
            [0, 0x55, 0x55, 0x55, 0x55, 0x55, 0x55, 0x55, 0x55, 0x55, 0x55, 0x55, 0, 0, 0],
            0
        );

        $this->fifo->onChunk([$this, 'consumeChunk']);
    }

    /**
     * Consumes a chunk from the FIFO.
     *
     * @param object $chunk
     */
    public function consumeChunk($chunk)
    {
        $this->fifo->pause();

        $this->receiveBuffer = $this->bufferClass->concat([$this->receiveBuffer, $chunk]);

        while ($this->receiveBuffer->byteLength >= 992) {
            $slice = $this->receiveBuffer->subarray(0, 992);
            $this->receiveBuffer = $this->receiveBuffer->subarray(992);

            $slice->copy($this->audioBuffer, 16);

            $this->audioSession->send($this->bufferClass->from($this->audioBuffer));
        }

        $this->fifo->resume();
    }
}
