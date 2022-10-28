<?php

/**
 * KVM & HDMI capture with TypeScript and PHP using Uniter.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/hdmpi
 *
 * Released under the MIT license
 * https://github.com/asmblah/hdmpi/raw/master/MIT-LICENSE.txt
 */

namespace Asmblah\Hdmpi\Client;

use Asmblah\Hdmpi\Worker\WorkerInterface;

/**
 * Class Client.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class Client
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
     * Starts all workers for this client.
     */
    public function start()
    {
        foreach ($this->workers as $worker) {
            $worker->start();
        }
    }
}
