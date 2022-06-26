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
 * Class ProductionLogger.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ProductionLogger implements LoggerInterface
{
    /**
     * @var ConsoleLogger
     */
    private $consoleLogger;

    /**
     * @param ConsoleLogger $consoleLogger
     */
    public function __construct(ConsoleLogger $consoleLogger)
    {
        $this->consoleLogger = $consoleLogger;
    }

    /**
     * @inheritDoc
     */
    public function debug($message)
    {
        // Discard message.
    }

    /**
     * @inheritDoc
     */
    public function error($message)
    {
        // Always log error messages.
        $this->consoleLogger->error($message);
    }

    /**
     * @inheritDoc
     */
    public function info($message)
    {
        // Discard message.
    }

    /**
     * @inheritDoc
     */
    public function warn($message)
    {
        // Always log warning messages.
        $this->consoleLogger->warn($message);
    }
}
