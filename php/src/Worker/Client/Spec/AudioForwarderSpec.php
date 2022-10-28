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
 * Class AudioForwarderSpec.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class AudioForwarderSpec implements WorkerSpecInterface
{
    /**
     * @var int
     */
    private $audioPort;
    /**
     * @var string
     */
    private $fifoPath;
    /**
     * @var string
     */
    private $localHost;
    /**
     * @var string
     */
    private $multicastGroup;

    /**
     * @param string $localHost
     * @param string $multicastGroup
     * @param int $audioPort
     * @param string $fifoPath
     */
    public function __construct($localHost, $multicastGroup, $audioPort, $fifoPath)
    {
        $this->audioPort = $audioPort;
        $this->fifoPath = $fifoPath;
        $this->localHost = $localHost;
        $this->multicastGroup = $multicastGroup;
    }

    /**
     * @return int
     */
    public function getAudioPort()
    {
        return $this->audioPort;
    }

    /**
     * @return string
     */
    public function getFifoPath()
    {
        return $this->fifoPath;
    }

    /**
     * @return string
     */
    public function getLocalHost()
    {
        return $this->localHost;
    }

    /**
     * @return string
     */
    public function getMulticastGroup()
    {
        return $this->multicastGroup;
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'Client audio forwarder';
    }
}
