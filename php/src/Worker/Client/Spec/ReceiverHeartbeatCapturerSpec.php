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
 * Class ReceiverHeartbeatCapturerSpec.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ReceiverHeartbeatCapturerSpec implements WorkerSpecInterface
{
    /**
     * @var string
     */
    private $localHost;
    /**
     * @var number
     */
    private $port;

    /**
     * @param string $localHost
     * @param number $port
     */
    public function __construct($localHost, $port)
    {
        $this->localHost = $localHost;
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getLocalHost()
    {
        return $this->localHost;
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'Client receiver heartbeat capturer';
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }
}
