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

use Asmblah\Hdmpi\Io\IoFactory;
use Asmblah\Hdmpi\Logger\ConsoleLogger;
use Asmblah\Hdmpi\Logger\ProductionLogger;
use Asmblah\Hdmpi\Session\SessionFactory;
use Asmblah\Hdmpi\Tools\BufferTools;
use Asmblah\Hdmpi\Worker\Client\Forwarder\Av\AudioForwarderFactory;
use Asmblah\Hdmpi\Worker\Client\Forwarder\Av\VideoForwarderFactory;
use Asmblah\Hdmpi\Worker\Client\Heartbeat\ReceiverHeartbeatCapturerFactory;
use Asmblah\Hdmpi\Worker\Client\Heartbeat\TransmitterHeartbeatSenderFactory;
use Asmblah\Hdmpi\Worker\Client\Spec\AudioForwarderSpec;
use Asmblah\Hdmpi\Worker\Client\Spec\ReceiverHeartbeatCapturerSpec;
use Asmblah\Hdmpi\Worker\Client\Spec\TransmitterHeartbeatSenderSpec;
use Asmblah\Hdmpi\Worker\Client\Spec\VideoForwarderSpec;
use Asmblah\Hdmpi\Worker\Server\Capturer\Av\AudioCapturerFactory;
use Asmblah\Hdmpi\Worker\Server\Capturer\Av\VideoCapturerFactory;
use Asmblah\Hdmpi\Worker\Server\Spec\AudioCapturerSpec;
use Asmblah\Hdmpi\Worker\Server\Spec\VideoCapturerSpec;
use Asmblah\Hdmpi\Worker\WorkerFactory;

require_once __DIR__ . '/../../vendor/autoload.php';

return function (
    callable $setTimeout,
    callable $createSocket,
    $bufferClass,
    callable $fsCreateReadStream,
    callable $fsCreateWriteStream,
    callable $writeToWriteStream,
    callable $bindSocket,
    callable $onSocketMessage,
    callable $sendToSocket
) {
    $logger = new ConsoleLogger();
    $logger = new ProductionLogger($logger);

    $bufferTools = new BufferTools($bufferClass);
    $ioFactory = new IoFactory($logger, $bufferClass, $writeToWriteStream);
    $sessionFactory = new SessionFactory(
        $logger,
        $createSocket,
        $bindSocket,
        $onSocketMessage,
        $sendToSocket
    );

    // Server workers.
    $workerFactory = new WorkerFactory();
    $workerFactory->registerFactory(
        VideoCapturerSpec::class,
        new VideoCapturerFactory(
            $logger,
            $ioFactory,
            $sessionFactory,
            $fsCreateWriteStream
        )
    );
    $workerFactory->registerFactory(
        AudioCapturerSpec::class,
        new AudioCapturerFactory(
            $logger,
            $ioFactory,
            $sessionFactory,
            $bufferClass,
            $fsCreateWriteStream
        )
    );

    // Client workers.
    $workerFactory->registerFactory(
        AudioForwarderSpec::class,
        new AudioForwarderFactory(
            $logger,
            $bufferTools,
            $ioFactory,
            $sessionFactory,
            $bufferClass,
            $fsCreateReadStream
        )
    );
    $workerFactory->registerFactory(
        ReceiverHeartbeatCapturerSpec::class,
        new ReceiverHeartbeatCapturerFactory(
            $logger,
            $bufferTools,
            $sessionFactory,
            $bufferClass
        )
    );
    $workerFactory->registerFactory(
        TransmitterHeartbeatSenderSpec::class,
        new TransmitterHeartbeatSenderFactory(
            $logger,
            $bufferTools,
            $sessionFactory,
            $bufferClass,
            $setTimeout
        )
    );
    $workerFactory->registerFactory(
        VideoForwarderSpec::class,
        new VideoForwarderFactory(
            $logger,
            $bufferTools,
            $ioFactory,
            $sessionFactory,
            $bufferClass,
            $fsCreateReadStream
        )
    );

    return new Hdmpi($workerFactory);
};
