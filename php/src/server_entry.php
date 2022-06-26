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
use Asmblah\Hdmpi\Capturer\Video\AudioCapturerFactory;
use Asmblah\Hdmpi\Capturer\Video\VideoCapturerFactory;
use Asmblah\Hdmpi\Logger\ConsoleLogger;
use Asmblah\Hdmpi\Logger\ProductionLogger;

require_once __DIR__ . '/../../vendor/autoload.php';

return function (callable $createSocket, $bufferClass, $fsCreateWriteStream, $writeToWriteStream) {
    $logger = new ConsoleLogger();
//    $logger = new ProductionLogger($logger);

    $capturerFactory = new CapturerFactory($logger, $createSocket);
    $capturerFactory->registerFactory(
        VideoCapturerSpec::class,
        new VideoCapturerFactory($logger, $bufferClass, $fsCreateWriteStream, $writeToWriteStream)
    );
    $capturerFactory->registerFactory(
        AudioCapturerSpec::class,
        new AudioCapturerFactory($logger, $bufferClass, $fsCreateWriteStream, $writeToWriteStream)
    );

    return new Hdmpi($capturerFactory);
};
