<?php

/**
 * KVM & HDMI capture with TypeScript and PHP using Uniter.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/hdmpi
 *
 * Released under the MIT license
 * https://github.com/asmblah/hdmpi/raw/master/MIT-LICENSE.txt
 */

namespace Asmblah\Hdmpi\Logger;

/**
 * Class ConsoleLogger.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ConsoleLogger implements LoggerInterface
{
    /**
     * @inheritDoc
     */
    public function debug($message)
    {
        print '[DEBUG] ' . $message . PHP_EOL;
    }

    /**
     * @inheritDoc
     */
    public function error($message)
    {
        print '[ERROR] ' . $message . PHP_EOL;
    }

    /**
     * @inheritDoc
     */
    public function info($message)
    {
        print $message . PHP_EOL;
    }

    /**
     * @inheritDoc
     */
    public function warn($message)
    {
        print '[WARNING] ' . $message . PHP_EOL;
    }
}
