<?php

/**
 * KVM & HDMI capture with TypeScript and PHP using Uniter.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/hdmpi
 *
 * Released under the MIT license
 * https://github.com/asmblah/hdmpi/raw/master/MIT-LICENSE.txt
 */

namespace Asmblah\Hdmpi\Worker\Client\Heartbeat;

use Asmblah\Hdmpi\Logger\LoggerInterface;
use Asmblah\Hdmpi\Session\BroadcastSessionInterface;
use Asmblah\Hdmpi\Tools\BufferTools;
use Asmblah\Hdmpi\Worker\WorkerInterface;

/**
 * Class TransmitterHeartbeatSender.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class TransmitterHeartbeatSender implements WorkerInterface
{
    /**
     * @var object
     */
    private $bufferClass;
    /**
     * @var BufferTools
     */
    private $bufferTools;
    /**
     * @var int
     */
    private $heartbeatCounter = 0;
    /**
     * @var object
     */
    private $heartbeatData;
    /**
     * @var BroadcastSessionInterface
     */
    private $heartbeatSession;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var callable
     */
    private $setTimeout;

    /**
     * @param LoggerInterface $logger
     * @param BufferTools $bufferTools
     * @param BroadcastSessionInterface $heartbeatSession
     * @param object $bufferClass
     * @param callable $setTimeout
     */
    public function __construct(
        LoggerInterface $logger,
        BufferTools $bufferTools,
        BroadcastSessionInterface $heartbeatSession,
        $bufferClass,
        callable $setTimeout
    ) {
        $this->bufferClass = $bufferClass;
        $this->bufferTools = $bufferTools;
        $this->heartbeatSession = $heartbeatSession;
        $this->logger = $logger;
        $this->setTimeout = $setTimeout;
    }

    /**
     * @inheritDoc
     */
    public function start()
    {
        $this->logger->debug('Starting client transmitter heartbeat sender');

        $this->heartbeatData = $this->bufferClass->alloc(512);

        $this->heartbeatSession->start();

        $this->sendHeartbeat();
    }

    public function sendHeartbeat()
    {
        // FIXME: Should be uptime/time since start instead.
        $timestamp = microtime(true) & 0xffffffff;

        $position = 0;

        /**
         * @param int[] $bytes
         */
        $writeBytes = function ($bytes) use (&$position) {
            foreach ($bytes as $byte) {
                $this->heartbeatData->writeInt8($byte, $position++);
            }
        };

        /**
         * @param object $buffer
         */
        $writeBuffer = function ($buffer) use (&$position) {
            $buffer->copy($this->heartbeatData, $position);

            $position += $buffer->byteLength;
        };

        $writeBytes([0x54, 0x46, 0x36, 0x7a, 0x63, 0x01, 0x00, 0x00]);
        $writeBuffer($this->bufferTools->numberTo16BitLittleEndian($this->heartbeatCounter));
        $writeBytes([0x00, 0x03, 0x03, 0x03, 0x00, 0x24, 0x00, 0x00]);
        $writeBytes([0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00]);
        $writeBytes([3]);
        $writeBuffer($this->bufferTools->numberTo16BitBigEndian(1920));
        $writeBuffer($this->bufferTools->numberTo16BitBigEndian(1080 / 2));
        $writeBuffer($this->bufferTools->numberTo16BitBigEndian(499));
        $writeBuffer($this->bufferTools->numberTo16BitBigEndian(1280));
        $writeBuffer($this->bufferTools->numberTo16BitBigEndian(1080 / 2));
        $writeBuffer($this->bufferTools->numberTo16BitBigEndian(120));
        $writeBuffer($this->bufferTools->numberTo32BitBigEndian($timestamp));
        $writeBytes([0, 1, 0, 0, 0, 0, 2, 0x0a]);

        $this->heartbeatCounter = ($this->heartbeatCounter + 1) & 0xffff;

        $this->heartbeatSession->send($this->heartbeatData);

        $sender = $this;
        ($this->setTimeout)(function () use ($sender) {
            $sender->sendHeartbeat();
        }, 1000);
    }
}
