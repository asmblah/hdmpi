<?php

/**
 * KVM & HDMI capture with TypeScript and PHP using Uniter.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/hdmpi
 *
 * Released under the MIT license
 * https://github.com/asmblah/hdmpi/raw/master/MIT-LICENSE.txt
 */

namespace Asmblah\Hdmpi\Capturer;

use Asmblah\Hdmpi\Capturer\Spec\CapturerSpecInterface;
use Asmblah\Hdmpi\Logger\LoggerInterface;
use Asmblah\Hdmpi\Session\MulticastClientSession;
use RuntimeException;

/**
 * Class CapturerFactory.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class CapturerFactory
{
    /**
     * @var callable
     */
    private $createSocket;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var CapturerFactoryInterface[]
     */
    private $subFactories = [];

    /**
     * @param LoggerInterface $logger
     * @param callable $createSocket
     */
    public function __construct(LoggerInterface $logger, callable $createSocket)
    {
        $this->createSocket = $createSocket;
        $this->logger = $logger;
    }

    /**
     * Registers a factory for capturers of the given spec type.
     *
     * @param string $specFqcn
     * @param CapturerFactoryInterface $capturerFactory
     */
    public function registerFactory(
        $specFqcn,
        CapturerFactoryInterface $capturerFactory
    ) {
        $this->subFactories[$specFqcn] = $capturerFactory;
    }

    /**
     * Creates a capturer from the given spec.
     *
     * @param CapturerSpecInterface $capturerSpec
     * @param int $receiveBufferSizeBytes
     * @return CapturerInterface
     */
    public function createCapturer(CapturerSpecInterface $capturerSpec, $receiveBufferSizeBytes)
    {
        $nativeUdp4Socket = ($this->createSocket)('udp4');

        $session = new MulticastClientSession(
            $this->logger,
            $nativeUdp4Socket,
            $capturerSpec->getLocalHost(),
            $capturerSpec->getMulticastGroup(),
            $capturerSpec->getPort(),
            $capturerSpec->getMaxMessageBacklog(),
            $receiveBufferSizeBytes
        );

        $specFqcn = $capturerSpec::class;

        if (!array_key_exists($specFqcn, $this->subFactories)) {
            throw new RuntimeException(
                sprintf(
                    'No factory registered for spec type "%s"',
                    $specFqcn
                )
            );
        }

        return $this->subFactories[$specFqcn]
            ->createCapturer($capturerSpec, $session);
    }
}
