<?php

/**
 * KVM & HDMI capture with TypeScript and PHP using Uniter.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/hdmpi
 *
 * Released under the MIT license
 * https://github.com/asmblah/hdmpi/raw/master/MIT-LICENSE.txt
 */

namespace Asmblah\Hdmpi\Capturer\Spec;

/**
 * Interface CapturerSpecInterface.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface CapturerSpecInterface
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
     * @return int
     */
    public function getMaxMessageBacklog();

    /**
     * @return string
     */
    public function getMulticastGroup();

    /**
     * @return int
     */
    public function getPort();
}
