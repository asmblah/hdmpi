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

use Asmblah\Hdmpi\Io\IoFactoryInterface;
use Asmblah\Hdmpi\Logger\LoggerInterface;
use Asmblah\Hdmpi\Session\SessionFactoryInterface;
use Asmblah\Hdmpi\Tools\BufferTools;
use Asmblah\Hdmpi\Worker\Client\Spec\AudioForwarderSpec;
use Asmblah\Hdmpi\Worker\WorkerSubFactoryInterface;

/**
 * Class AudioForwarderFactory.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class AudioForwarderFactory implements WorkerSubFactoryInterface
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
     * @var callable
     */
    private $fsCreateReadStream;
    /**
     * @var IoFactoryInterface
     */
    private $ioFactory;
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
     * @param IoFactoryInterface $ioFactory
     * @param SessionFactoryInterface $sessionFactory
     * @param object $bufferClass
     * @param callable $fsCreateReadStream
     */
    public function __construct(
        LoggerInterface $logger,
        BufferTools $bufferTools,
        IoFactoryInterface $ioFactory,
        SessionFactoryInterface $sessionFactory,
        $bufferClass,
        callable $fsCreateReadStream
    ) {
        $this->bufferClass = $bufferClass;
        $this->bufferTools = $bufferTools;
        $this->fsCreateReadStream = $fsCreateReadStream;
        $this->ioFactory = $ioFactory;
        $this->logger = $logger;
        $this->sessionFactory = $sessionFactory;
    }

    /**
     * @inheritDoc
     */
    public function createForwarder(AudioForwarderSpec $forwarderSpec)
    {
        $audioSession = $this->sessionFactory->createMulticastSession(
            $forwarderSpec->getLocalHost(),
            $forwarderSpec->getLocalHost(),
            $forwarderSpec->getMulticastGroup(),
            $forwarderSpec->getAudioPort()
        );

        $fsCreateReadStream = $this->fsCreateReadStream;

        $openStream = function () use ($forwarderSpec, $fsCreateReadStream) {
            return $fsCreateReadStream($forwarderSpec->getFifoPath());
        };

        $fifo = $this->ioFactory->createFifo($openStream);

        return new AudioForwarder(
            $this->logger,
            $this->bufferTools,
            $audioSession,
            $fifo,
            $this->bufferClass
        );
    }

    /**
     * @inheritDoc
     */
    public function getFactoryCallable()
    {
        return [$this, 'createForwarder'];
    }
}
