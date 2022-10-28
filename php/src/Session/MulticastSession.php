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
 * Class MulticastSession.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class MulticastSession implements MulticastSessionInterface
{
    /**
     * @var callable
     */
    private $bindSocket;
    /**
     * @var string
     */
    private $boundAddress;
    /**
     * @var string
     */
    private $localHost;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var string
     */
    private $multicastGroup;
    /**
     * @var object
     */
    private $nativeUdp4Socket;
    /**
     * @var callable|null
     */
    private $onDatagramCallback;
    /**
     * @var callable
     */
    private $onSocketMessage;
    /**
     * @var int
     */
    private $port;
    /**
     * @var callable
     */
    private $sendToSocket;

    /**
     * @param LoggerInterface $logger
     * @param callable $bindSocket
     * @param callable $onSocketMessage
     * @param callable $sendToSocket
     * @param object $nativeUdp4Socket
     * @param string $localHost
     * @param string $boundAddress
     * @param string $multicastGroup
     * @param int $port
     */
    public function __construct(
        LoggerInterface $logger,
        callable $bindSocket,
        callable $onSocketMessage,
        callable $sendToSocket,
        $nativeUdp4Socket,
        $localHost,
        $boundAddress,
        $multicastGroup,
        $port
    ) {
        $this->bindSocket = $bindSocket;
        $this->boundAddress = $boundAddress;
        $this->localHost = $localHost;
        $this->logger = $logger;
        $this->multicastGroup = $multicastGroup;
        $this->nativeUdp4Socket = $nativeUdp4Socket;
        $this->onSocketMessage = $onSocketMessage;
        $this->port = $port;
        $this->sendToSocket = $sendToSocket;
    }

    /**
     * @inheritDoc
     */
    public function capture(callable $onDatagram)
    {
        $this->onDatagramCallback = $onDatagram;
    }

    /**
     * @inheritDoc
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @inheritDoc
     */
    public function send($datagram)
    {
        ($this->sendToSocket)($this->nativeUdp4Socket, $datagram, $this->port, $this->multicastGroup);
    }

    /**
     * @inheritDoc
     */
    public function start()
    {
        $logger = $this->logger;
        $nativeUdp4Socket = $this->nativeUdp4Socket;

        $localHost = $this->localHost;
        $multicastGroup = $this->multicastGroup;

        $onDatagramCallback =& $this->onDatagramCallback;

        ($this->onSocketMessage)($nativeUdp4Socket, function ($message, $remoteInfo) use (
            &$onDatagramCallback
        ) {
            if ($onDatagramCallback) {
                $onDatagramCallback($message, $remoteInfo);
            }
        });

        $nativeUdp4Socket->on('error', function ($error) use ($logger) {
            $logger->error('MulticastSession :: UDP multicast socket error: ' . $error->toString());
        });

        ($this->bindSocket)($nativeUdp4Socket, $this->port, $this->boundAddress);

        $address = $nativeUdp4Socket->address();

        $nativeUdp4Socket->setBroadcast(true);
        $nativeUdp4Socket->setMulticastTTL(128);
        $nativeUdp4Socket->addMembership($multicastGroup, $localHost);

        $logger->info(
            'MulticastSession :: UDP multicast client listening on ' .
            $address['address'] . ':' . $address['port'] .
            ', multicast group ' . $multicastGroup
        );
    }
}
