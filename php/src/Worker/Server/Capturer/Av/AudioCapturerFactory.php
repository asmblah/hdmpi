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

use Asmblah\Hdmpi\Io\IoFactoryInterface;
use Asmblah\Hdmpi\Logger\LoggerInterface;
use Asmblah\Hdmpi\Session\SessionFactoryInterface;
use Asmblah\Hdmpi\Worker\Server\Spec\AudioCapturerSpec;
use Asmblah\Hdmpi\Worker\WorkerSubFactoryInterface;

/**
 * Class AudioCapturerFactory.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class AudioCapturerFactory implements WorkerSubFactoryInterface
{
    /**
     * @var object
     */
    private $bufferClass;
    /**
     * @var callable
     */
    private $fsCreateWriteStream;
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
     * @param IoFactoryInterface $ioFactory
     * @param SessionFactoryInterface $sessionFactory
     * @param object $bufferClass
     * @param callable $fsCreateWriteStream
     */
    public function __construct(
        LoggerInterface $logger,
        IoFactoryInterface $ioFactory,
        SessionFactoryInterface $sessionFactory,
        $bufferClass,
        callable $fsCreateWriteStream
    ) {
        $this->bufferClass = $bufferClass;
        $this->fsCreateWriteStream = $fsCreateWriteStream;
        $this->ioFactory = $ioFactory;
        $this->logger = $logger;
        $this->sessionFactory = $sessionFactory;
    }

    /**
     * @inheritDoc
     */
    public function createCapturer(AudioCapturerSpec $capturerSpec)
    {
        $session = $this->sessionFactory->createMulticastSession(
            $capturerSpec->getLocalHost(),
            /*
             * Note that different to documentation in various places, the host here must be
             * the multicast group and not the local host
             * (thanks to https://stackoverflow.com/questions/45044189/node-js-multicast-client).
             */
            $capturerSpec->getMulticastGroup(),
            $capturerSpec->getMulticastGroup(),
            $capturerSpec->getPort()
        );

        $fsCreateWriteStream = $this->fsCreateWriteStream;

        $openStream = function () use ($capturerSpec, $fsCreateWriteStream) {
            $writeStream = $fsCreateWriteStream($capturerSpec->getFifoPath(), [
                'flags' => 'a',
                'highWaterMark' => 256,
            ]);

            return $writeStream;
        };

        $fifo = $this->ioFactory->createFifo($openStream);

        return new AudioCapturer(
            $this->logger,
            $session,
            $fifo,
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
