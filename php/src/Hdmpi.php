<?php

/**
 * KVM & HDMI capture with TypeScript and PHP using Uniter.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/hdmpi
 *
 * Released under the MIT license
 * https://github.com/asmblah/hdmpi/raw/master/MIT-LICENSE.txt
 */

namespace Asmblah\Hdmpi;

use Asmblah\Hdmpi\Capturer\CapturerFactory;
use Asmblah\Hdmpi\Capturer\Spec\AudioCapturerSpec;
use Asmblah\Hdmpi\Capturer\Spec\VideoCapturerSpec;
use Asmblah\Hdmpi\Server\Server;

/**
 * Class Hdmpi.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class Hdmpi
{
    /**
     * @var CapturerFactory
     */
    private $capturerFactory;

    /**
     * @param CapturerFactory $capturerFactory
     */
    public function __construct(CapturerFactory $capturerFactory)
    {
        $this->capturerFactory = $capturerFactory;
    }

    public function createAudioServer(
        $localHost,
        $multicastGroup,
        $audioPort,
        $audioFifoPath,
        $maxMessageBacklog,
        $receiveBufferSizeBytes
    ) {
        $audioCapturer = $this->capturerFactory->createCapturer(
            new AudioCapturerSpec($localHost, $multicastGroup, $audioPort, $audioFifoPath, $maxMessageBacklog),
            $receiveBufferSizeBytes
        );

        return new Server([$audioCapturer]);
    }

    public function createVideoServer(
        $localHost,
        $multicastGroup,
        $videoPort,
        $videoFifoPath,
        $maxMessageBacklog,
        $receiveBufferSizeBytes
    ) {
        $videoCapturer = $this->capturerFactory->createCapturer(
            new VideoCapturerSpec($localHost, $multicastGroup, $videoPort, $videoFifoPath, $maxMessageBacklog),
            $receiveBufferSizeBytes
        );

        return new Server([$videoCapturer]);
    }
}
