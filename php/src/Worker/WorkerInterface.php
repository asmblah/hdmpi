<?php

/**
 * KVM & HDMI capture with TypeScript and PHP using Uniter.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/hdmpi
 *
 * Released under the MIT license
 * https://github.com/asmblah/hdmpi/raw/master/MIT-LICENSE.txt
 */

namespace Asmblah\Hdmpi\Worker;

/**
 * Interface WorkerInterface.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface WorkerInterface
{
    public function start();
}
