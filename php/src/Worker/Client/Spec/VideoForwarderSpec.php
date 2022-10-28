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
 * Class VideoForwarderSpec.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class VideoForwarderSpec implements WorkerSpecInterface
{
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
     * @var int
     */
    private $videoPort;
    /**
     * @var int
     */
    private $videoSyncPort;

    /**
     * @param string $localHost
     * @param string $multicastGroup
     * @param int $videoPort
     * @param int $videoSyncPort
     * @param string $fifoPath
     */
    public function __construct($localHost, $multicastGroup, $videoPort, $videoSyncPort, $fifoPath)
    {
        $this->fifoPath = $fifoPath;
        $this->localHost = $localHost;
        $this->multicastGroup = $multicastGroup;
        $this->videoPort = $videoPort;
        $this->videoSyncPort = $videoSyncPort;
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
        return 'Client video forwarder';
    }

    /**
     * @return int
     */
    public function getVideoPort()
    {
        return $this->videoPort;
    }

    /**
     * @return int
     */
    public function getVideoSyncPort()
    {
        return $this->videoSyncPort;
    }
}
