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

use Asmblah\Hdmpi\Client\Client;
use Asmblah\Hdmpi\Server\Server;
use Asmblah\Hdmpi\Worker\Client\Spec\AudioForwarderSpec;
use Asmblah\Hdmpi\Worker\Client\Spec\ReceiverHeartbeatCapturerSpec;
use Asmblah\Hdmpi\Worker\Client\Spec\TransmitterHeartbeatSenderSpec;
use Asmblah\Hdmpi\Worker\Client\Spec\VideoForwarderSpec;
use Asmblah\Hdmpi\Worker\Server\Spec\AudioCapturerSpec;
use Asmblah\Hdmpi\Worker\Server\Spec\VideoCapturerSpec;
use Asmblah\Hdmpi\Worker\WorkerFactory;

/**
 * Class Hdmpi.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class Hdmpi
{
    /**
     * @var WorkerFactory
     */
    private $workerFactory;

    /**
     * @param WorkerFactory $workerFactory
     */
    public function __construct(WorkerFactory $workerFactory)
    {
        $this->workerFactory = $workerFactory;
    }

    /**
     * Creates a new audio forwarding client.
     *
     * @param string $localHost
     * @param string $multicastGroup
     * @param int $audioPort
     * @param string $audioFifoPath
     * @return Client
     */
    public function createAudioForwarderClient(
        $localHost,
        $multicastGroup,
        $audioPort,
        $audioFifoPath
    ) {
        $audioForwarder = $this->workerFactory->createWorker(
            new AudioForwarderSpec(
                $localHost,
                $multicastGroup,
                $audioPort,
                $audioFifoPath
            )
        );

        return new Client([$audioForwarder]);
    }

    /**
     * Creates a new audio processing server.
     *
     * @param string $localHost
     * @param string $multicastGroup
     * @param int $audioPort
     * @param string $audioFifoPath
     * @return Server
     */
    public function createAudioServer(
        $localHost,
        $multicastGroup,
        $audioPort,
        $audioFifoPath
    ) {
        $audioCapturer = $this->workerFactory->createWorker(
            new AudioCapturerSpec($localHost, $multicastGroup, $audioPort, $audioFifoPath)
        );

        return new Server([$audioCapturer]);
    }

    /**
     * Creates a new receiver heartbeat capturer client.
     *
     * @param string $localHost
     * @param int $heartbeatPort
     * @return Client
     */
    public function createReceiverHeartbeatCapturerClient(
        $localHost,
        $heartbeatPort
    ) {
        $heartbeatSender = $this->workerFactory->createWorker(
            new ReceiverHeartbeatCapturerSpec(
                $localHost,
                $heartbeatPort
            )
        );

        return new Client([$heartbeatSender]);
    }

    /**
     * Creates a new transmitter heartbeat sender client.
     *
     * @param string $broadcastAddress
     * @param int $heartbeatPort
     * @return Client
     */
    public function createTransmitterHeartbeatSenderClient(
        $broadcastAddress,
        $heartbeatPort
    ) {
        $heartbeatSender = $this->workerFactory->createWorker(
            new TransmitterHeartbeatSenderSpec(
                $broadcastAddress,
                $heartbeatPort
            )
        );

        return new Client([$heartbeatSender]);
    }

    /**
     * Creates a new video forwarding client.
     *
     * @param string $localHost
     * @param string $multicastGroup
     * @param int $videoPort
     * @param int $videoSyncPort
     * @param string $videoFifoPath
     * @return Client
     */
    public function createVideoForwarderClient(
        $localHost,
        $multicastGroup,
        $videoPort,
        $videoSyncPort,
        $videoFifoPath
    ) {
        $videoForwarder = $this->workerFactory->createWorker(
            new VideoForwarderSpec(
                $localHost,
                $multicastGroup,
                $videoPort,
                $videoSyncPort,
                $videoFifoPath
            )
        );

        return new Client([$videoForwarder]);
    }

    /**
     * Creates a new video processing server.
     *
     * @param string $localHost
     * @param string $multicastGroup
     * @param int $videoPort
     * @param int $videoSyncPort
     * @param string $videoFifoPath
     * @return Server
     */
    public function createVideoServer(
        $localHost,
        $multicastGroup,
        $videoPort,
        $videoSyncPort,
        $videoFifoPath
    ) {
        $videoCapturer = $this->workerFactory->createWorker(
            new VideoCapturerSpec($localHost, $multicastGroup, $videoPort, $videoSyncPort, $videoFifoPath)
        );

        return new Server([$videoCapturer]);
    }
}
