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
use Asmblah\Hdmpi\Session\SessionFactoryInterface;
use Asmblah\Hdmpi\Tools\BufferTools;
use Asmblah\Hdmpi\Worker\Client\Spec\ReceiverHeartbeatCapturerSpec;
use Asmblah\Hdmpi\Worker\WorkerSubFactoryInterface;

/**
 * Class ReceiverHeartbeatCapturerFactory.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ReceiverHeartbeatCapturerFactory implements WorkerSubFactoryInterface
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
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var SessionFactoryInterface
     */
    private $sessionFactory;

    /**
     * @param LoggerInterface $logger
     * @param BufferTools $bufferTools
     * @param SessionFactoryInterface $sessionFactory
     * @param object $bufferClass
     */
    public function __construct(
        LoggerInterface $logger,
        BufferTools $bufferTools,
        SessionFactoryInterface $sessionFactory,
        $bufferClass
    ) {
        $this->bufferClass = $bufferClass;
        $this->bufferTools = $bufferTools;
        $this->logger = $logger;
        $this->sessionFactory = $sessionFactory;
    }

    /**
     * @inheritDoc
     */
    public function createCapturer(ReceiverHeartbeatCapturerSpec $senderSpec)
    {
        $heartbeatSession = $this->sessionFactory->createUnicastSession(
            $senderSpec->getLocalHost(),
            $senderSpec->getPort()
        );

        return new ReceiverHeartbeatCapturer(
            $this->logger,
            $this->bufferTools,
            $heartbeatSession,
            $this->bufferClass
        );
    }

    /**
     * @inheritDoc
     */
    public function getFactoryCallable()
    {
        return [$this, 'createCapturer'];
    }
}
