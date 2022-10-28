<?php

/**
 * KVM & HDMI capture with TypeScript and PHP using Uniter.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/hdmpi
 *
 * Released under the MIT license
 * https://github.com/asmblah/hdmpi/raw/master/MIT-LICENSE.txt
 */

namespace Asmblah\Hdmpi\Worker\Client\Heartbeat;

use Asmblah\Hdmpi\Logger\LoggerInterface;
use Asmblah\Hdmpi\Session\UnicastSessionInterface;
use Asmblah\Hdmpi\Tools\BufferTools;
use Asmblah\Hdmpi\Worker\WorkerInterface;

/**
 * Class ReceiverHeartbeatCapturer.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ReceiverHeartbeatCapturer implements WorkerInterface
{
    /**
     * @var object
     */
    private $bufferClass;
    /**
     * @var BufferTools
     */
    private $bufferTools;
    /**
     * @var int
     */
    private $heartbeatCounter = 0;
    /**
     * @var object
     */
    private $heartbeatData;
    /**
     * @var UnicastSessionInterface
     */
    private $heartbeatSession;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     * @param BufferTools $bufferTools
     * @param UnicastSessionInterface $heartbeatSession
     * @param object $bufferClass
     */
    public function __construct(
        LoggerInterface $logger,
        BufferTools $bufferTools,
        UnicastSessionInterface $heartbeatSession,
        $bufferClass
    ) {
        $this->bufferClass = $bufferClass;
        $this->bufferTools = $bufferTools;
        $this->heartbeatSession = $heartbeatSession;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function start()
    {
        $this->logger->debug('Starting client receiver heartbeat capturer');

        $this->heartbeatSession->capture(function () {
            // Discard heartbeats from receiver -> transmitter (for now!)
            // FIXME: Forward them on!

            $this->logger->debug('Discarding receiver heartbeat (for now)');
        });

        $this->heartbeatSession->start();
    }
}
