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
 * Class UnicastSession.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class UnicastSession implements UnicastSessionInterface
{
    /**
     * @var string
     */
    private $address;
    /**
     * @var callable
     */
    private $bindSocket;
    /**
     * @var LoggerInterface
     */
    private $logger;
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
     * @param string $address
     * @param int $port
     */
    public function __construct(
        LoggerInterface $logger,
        callable $bindSocket,
        callable $onSocketMessage,
        callable $sendToSocket,
        $nativeUdp4Socket,
        $address,
        $port
    ) {
        $this->address = $address;
        $this->bindSocket = $bindSocket;
        $this->logger = $logger;
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
        ($this->sendToSocket)($this->nativeUdp4Socket, $datagram, $this->port, $this->address);
    }

    /**
     * @inheritDoc
     */
    public function start()
    {
        $logger = $this->logger;
        $nativeUdp4Socket = $this->nativeUdp4Socket;

        $onDatagramCallback =& $this->onDatagramCallback;

        ($this->onSocketMessage)($nativeUdp4Socket, function ($message, $remoteInfo) use (
            &$onDatagramCallback
        ) {
            if ($onDatagramCallback) {
                $onDatagramCallback($message, $remoteInfo);
            }
        });

        $nativeUdp4Socket->on('error', function ($error) use ($logger) {
            $logger->error('UnicastSession :: UDP unicast socket error: ' . $error->toString());
        });

        ($this->bindSocket)($nativeUdp4Socket, $this->port, '255.255.255.255');

        $address = $nativeUdp4Socket->address();

        $logger->info(
            'UnicastSession :: UDP unicast client listening on ' .
            $address['address'] . ':' . $address['port']
        );
    }
}
