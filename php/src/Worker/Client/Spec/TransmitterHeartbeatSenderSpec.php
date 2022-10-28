<?php

/**
 * KVM & HDMI capture with TypeScript and PHP using Uniter.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/hdmpi
 *
 * Released under the MIT license
 * https://github.com/asmblah/hdmpi/raw/master/MIT-LICENSE.txt
 */

namespace Asmblah\Hdmpi\Worker\Client\Spec;

use Asmblah\Hdmpi\Worker\Spec\WorkerSpecInterface;

/**
 * Class TransmitterHeartbeatSenderSpec.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class TransmitterHeartbeatSenderSpec implements WorkerSpecInterface
{
    /**
     * @var string
     */
    private $broadcastAddress;
    /**
     * @var number
     */
    private $port;

    /**
     * @param string $broadcastAddress
     * @param number $port
     */
    public function __construct($broadcastAddress, $port)
    {
        $this->broadcastAddress = $broadcastAddress;
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getBroadcastAddress()
    {
        return $this->broadcastAddress;
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'Client transmitter heartbeat sender';
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }
}
