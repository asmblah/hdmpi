<?php

/**
 * KVM & HDMI capture with TypeScript and PHP using Uniter.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/hdmpi
 *
 * Released under the MIT license
 * https://github.com/asmblah/hdmpi/raw/master/MIT-LICENSE.txt
 */

namespace Asmblah\Hdmpi\Capturer\Spec;

/**
 * Class VideoCapturerSpec.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class VideoCapturerSpec implements CapturerSpecInterface
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
    private $port;

    /**
     * @param string $localHost
     * @param string $multicastGroup
     * @param int $port
     * @param string $fifoPath
     */
    public function __construct($localHost, $multicastGroup, $port, $fifoPath)
    {
        $this->fifoPath = $fifoPath;
        $this->localHost = $localHost;
        $this->multicastGroup = $multicastGroup;
        $this->port = $port;
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
    public function getPort()
    {
        return $this->port;
    }
}
