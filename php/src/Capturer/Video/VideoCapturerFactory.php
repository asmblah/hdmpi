<?php

/**
 * KVM & HDMI capture with TypeScript and PHP using Uniter.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/hdmpi
 *
 * Released under the MIT license
 * https://github.com/asmblah/hdmpi/raw/master/MIT-LICENSE.txt
 */

namespace Asmblah\Hdmpi\Capturer\Video;

use Asmblah\Hdmpi\Capturer\CapturerFactoryInterface;
use Asmblah\Hdmpi\Capturer\Spec\CapturerSpecInterface;
use Asmblah\Hdmpi\Fifo;
use Asmblah\Hdmpi\Logger\LoggerInterface;
use Asmblah\Hdmpi\Session\SessionInterface;

/**
 * Class VideoCapturerFactory.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class VideoCapturerFactory implements CapturerFactoryInterface
{
    /**
     * @var object
     */
    private $bufferClass;
    /**
     * @var callable
     */
    private $fsCreateWriteStream;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var callable
     */
    private $writeToWriteStream;

    /**
     * @param LoggerInterface $logger
     * @param object $bufferClass
     * @param callable $fsCreateWriteStream
     * @param callable $writeToWriteStream
     */
    public function __construct(
        LoggerInterface $logger,
        $bufferClass,
        callable $fsCreateWriteStream,
        callable $writeToWriteStream
    ) {
        $this->bufferClass = $bufferClass;
        $this->fsCreateWriteStream = $fsCreateWriteStream;
        $this->logger = $logger;
        $this->writeToWriteStream = $writeToWriteStream;
    }

    /**
     * @inheritDoc
     */
    public function createCapturer(
        CapturerSpecInterface $capturerSpec,
        SessionInterface $session
    ) {
        $fsCreateWriteStream = $this->fsCreateWriteStream;

        $openStream = function () use ($capturerSpec, $fsCreateWriteStream) {
            $writeStream = $fsCreateWriteStream($capturerSpec->getFifoPath(), [
                'flags' => 'a',
                'highWaterMark' => 256,
            ]);

            return $writeStream;
        };

        $fifo = new Fifo(
            $this->logger,
            $this->bufferClass,
            $openStream,
            $this->writeToWriteStream
        );

        return new VideoCapturer(
            $this->logger,
            $session,
            $fifo,
            $this->bufferClass
        );
    }
}
