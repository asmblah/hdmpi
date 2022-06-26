<?php

/**
 * KVM & HDMI capture with TypeScript and PHP using Uniter.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/hdmpi
 *
 * Released under the MIT license
 * https://github.com/asmblah/hdmpi/raw/master/MIT-LICENSE.txt
 */

namespace Asmblah\Hdmpi\Server;

use Asmblah\Hdmpi\Capturer\CapturerInterface;

/**
 * Class Server.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class Server
{
    /**
     * @var CapturerInterface[]
     */
    private $capturers;

    /**
     * @param CapturerInterface[] $capturers
     */
    public function __construct(array $capturers)
    {
        $this->capturers = $capturers;
    }

    /**
     * Starts all capturers for this server.
     */
    public function start()
    {
        foreach ($this->capturers as $capturer) {
            $capturer->capture();
        }
    }
}
