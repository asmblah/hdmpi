<?php

/**
 * KVM & HDMI capture with TypeScript and PHP using Uniter.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/hdmpi
 *
 * Released under the MIT license
 * https://github.com/asmblah/hdmpi/raw/master/MIT-LICENSE.txt
 */

namespace Asmblah\Hdmpi\Session;

use Asmblah\Hdmpi\Logger\LoggerInterface;

/**
 * Class SessionFactory.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class SessionFactory implements SessionFactoryInterface
{
    /**
     * @var callable
     */
    private $bindSocket;
    /**
     * @var callable
     */
    private $createSocket;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var callable
     */
    private $onSocketMessage;
    /**
     * @var callable
     */
    private $sendToSocket;

    /**
     * @param LoggerInterface $logger
     * @param callable $createSocket
     * @param callable $bindSocket
     * @param callable $onSocketMessage
     * @param callable $sendToSocket
     */
    public function __construct(
        LoggerInterface $logger,
        callable $createSocket,
        callable $bindSocket,
        callable $onSocketMessage,
        callable $sendToSocket
    ) {
        $this->bindSocket = $bindSocket;
        $this->createSocket = $createSocket;
        $this->logger = $logger;
        $this->onSocketMessage = $onSocketMessage;
        $this->sendToSocket = $sendToSocket;
    }

    /**
     * @inheritDoc
     */
    public function createBroadcastSession($broadcastAddress, $port)
    {
        $nativeUdp4Socket = ($this->createSocket)((object)[
            'type' => 'udp4',
            'reuseAddr' => true
        ]);

        $session = new BroadcastSession(
            $this->logger,
            $this->bindSocket,
            $this->onSocketMessage,
            $this->sendToSocket,
            $nativeUdp4Socket,
            $broadcastAddress,
            $port
        );

        return $session;
    }

    /**
     * @inheritDoc
     */
    public function createMulticastSession(
        $localHost,
        $boundAddress,
        $multicastGroup,
        $port
    ) {
        $nativeUdp4Socket = ($this->createSocket)((object)[
            'type' => 'udp4',
            'recvBufferSize' => 180224,
            'sendBufferSize' => 180224
        ]);

        $session = new MulticastSession(
            $this->logger,
            $this->bindSocket,
            $this->onSocketMessage,
            $this->sendToSocket,
            $nativeUdp4Socket,
            $localHost,
            $boundAddress,
            $multicastGroup,
            $port
        );

        return $session;
    }

    /**
     * @inheritDoc
     */
    public function createUnicastSession($address, $port)
    {
        $nativeUdp4Socket = ($this->createSocket)((object)[
            'type' => 'udp4'
        ]);

        $session = new UnicastSession(
            $this->logger,
            $this->bindSocket,
            $this->onSocketMessage,
            $this->sendToSocket,
            $nativeUdp4Socket,
            $address,
            $port
        );

        return $session;
    }
}
