<?php

/**
 * KVM & HDMI capture with TypeScript and PHP using Uniter.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/hdmpi
 *
 * Released under the MIT license
 * https://github.com/asmblah/hdmpi/raw/master/MIT-LICENSE.txt
 */

namespace Asmblah\Hdmpi\Worker\Server\Spec;

/**
 * Class VideoCapturerSpec.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class VideoCapturerSpec implements AvSpecInterface
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
     * @inheritDoc
     */
    public function getFifoPath()
    {
        return $this->fifoPath;
    }

    /**
     * @inheritDoc
     */
    public function getLocalHost()
    {
        return $this->localHost;
    }

    /**
     * @inheritDoc
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
        return 'Server video capturer';
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
