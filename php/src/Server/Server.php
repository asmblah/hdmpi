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

use Asmblah\Hdmpi\Worker\WorkerInterface;

/**
 * Class Server.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class Server
{
    /**
     * @var WorkerInterface[]
     */
    private $workers;

    /**
     * @param WorkerInterface[] $workers
     */
    public function __construct(array $workers)
    {
        $this->workers = $workers;
    }

    /**
     * Starts all workers for this server.
     */
    public function start()
    {
        foreach ($this->workers as $worker) {
            $worker->start();
        }
    }
}
