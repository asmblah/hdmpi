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
use Asmblah\Hdmpi\Worker\Client\Spec\TransmitterHeartbeatSenderSpec;
use Asmblah\Hdmpi\Worker\WorkerSubFactoryInterface;

/**
 * Class TransmitterHeartbeatSenderFactory.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class TransmitterHeartbeatSenderFactory implements WorkerSubFactoryInterface
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
     * @var callable
     */
    private $setTimeout;

    /**
     * @param LoggerInterface $logger
     * @param BufferTools $bufferTools
     * @param SessionFactoryInterface $sessionFactory
     * @param object $bufferClass
     * @param callable $setTimeout
     */
    public function __construct(
        LoggerInterface $logger,
        BufferTools $bufferTools,
        SessionFactoryInterface $sessionFactory,
        $bufferClass,
        callable $setTimeout
    ) {
        $this->bufferClass = $bufferClass;
        $this->bufferTools = $bufferTools;
        $this->logger = $logger;
        $this->sessionFactory = $sessionFactory;
        $this->setTimeout = $setTimeout;
    }

    /**
     * @inheritDoc
     */
    public function createSender(TransmitterHeartbeatSenderSpec $senderSpec)
    {
        $heartbeatSession = $this->sessionFactory->createBroadcastSession(
            $senderSpec->getBroadcastAddress(),
            $senderSpec->getPort()
        );

        return new TransmitterHeartbeatSender(
            $this->logger,
            $this->bufferTools,
            $heartbeatSession,
            $this->bufferClass,
            $this->setTimeout
        );
    }

    /**
     * @inheritDoc
     */
    public function getFactoryCallable()
    {
        return [$this, 'createSender'];
    }
}
