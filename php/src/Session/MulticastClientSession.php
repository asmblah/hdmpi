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
 * Class MulticastClientSession.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class MulticastClientSession implements SessionInterface
{
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
     * @var int
     */
    private $port;

    /**
     * @param LoggerInterface $logger
     * @param object $nativeUdp4Socket
     * @param string $localHost
     * @param string $multicastGroup
     * @param int $port
     */
    public function __construct(
        LoggerInterface $logger,
        $nativeUdp4Socket,
        $localHost,
        $multicastGroup,
        $port
    ) {
        $this->localHost = $localHost;
        $this->logger = $logger;
        $this->multicastGroup = $multicastGroup;
        $this->nativeUdp4Socket = $nativeUdp4Socket;
        $this->port = $port;
    }

    /**
     * @inheritDoc
     */
    public function capture(callable $onPacket)
    {
        $logger = $this->logger;
        $nativeUdp4Socket = $this->nativeUdp4Socket;

        $localHost = $this->localHost;
        $multicastGroup = $this->multicastGroup;
        $busy = false;

        $nativeUdp4Socket->on('listening', function () use (
            $localHost,
            $logger,
            $multicastGroup,
            $nativeUdp4Socket
        ) {
            $address = $nativeUdp4Socket->address();

            $nativeUdp4Socket->setMulticastTTL(128);
            $nativeUdp4Socket->addMembership($multicastGroup, $localHost);

            $logger->info(
                'UDP Client listening on ' . $address['address'] . ':' . $address['port'] . ', multicast group ' . $multicastGroup
            );
        });

        $nativeUdp4Socket->on('message', function ($message, $remoteInfo) use (
            &$busy,
            $onPacket
        ) {
            if ($busy) {
                return;
            }

            $busy = true;
            $onPacket($message, $remoteInfo);
            $busy = false;
        });

        $nativeUdp4Socket->on('error', function ($error) use ($logger) {
            $logger->error('UDP socket error: ' . $error);
        });

        /*
         * Note that different to documentation in various places, the host here must be
         * the multicast group and not the local host
         * (thanks to https://stackoverflow.com/questions/45044189/node-js-multicast-client).
         */
        $nativeUdp4Socket->bind($this->port, $this->multicastGroup);
    }
}
