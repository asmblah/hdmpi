<?php

/**
 * KVM & HDMI capture with TypeScript and PHP using Uniter.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/hdmpi
 *
 * Released under the MIT license
 * https://github.com/asmblah/hdmpi/raw/master/MIT-LICENSE.txt
 */

namespace Asmblah\Hdmpi\Worker\Server\Spec;

use Asmblah\Hdmpi\Worker\Spec\WorkerSpecInterface;

/**
 * Interface AvSpecInterface.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface AvSpecInterface extends WorkerSpecInterface
{
    /**
     * @return string
     */
    public function getFifoPath();

    /**
     * @return string
     */
    public function getLocalHost();

    /**
     * @return string
     */
    public function getMulticastGroup();
}
