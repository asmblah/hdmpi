<?php

/**
 * KVM & HDMI capture with TypeScript and PHP using Uniter.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/hdmpi
 *
 * Released under the MIT license
 * https://github.com/asmblah/hdmpi/raw/master/MIT-LICENSE.txt
 */

namespace Asmblah\Hdmpi\Io;

use Asmblah\Hdmpi\Logger\LoggerInterface;

/**
 * Class IoFactory.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class IoFactory implements IoFactoryInterface
{
    /**
     * @var object
     */
    private $bufferClass;
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
     * @param callable $writeToWriteStream
     */
    public function __construct(
        LoggerInterface $logger,
        $bufferClass,
        callable $writeToWriteStream
    ) {
        $this->bufferClass = $bufferClass;
        $this->logger = $logger;
        $this->writeToWriteStream = $writeToWriteStream;
    }

    /**
     * @inheritDoc
     */
    public function createFifo(callable $openStream)
    {
        return new Fifo(
            $this->logger,
            $this->bufferClass,
            $openStream,
            $this->writeToWriteStream
        );
    }
}
