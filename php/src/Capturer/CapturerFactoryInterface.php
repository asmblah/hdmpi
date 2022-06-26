<?php

/**
 * KVM & HDMI capture with TypeScript and PHP using Uniter.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/hdmpi
 *
 * Released under the MIT license
 * https://github.com/asmblah/hdmpi/raw/master/MIT-LICENSE.txt
 */

namespace Asmblah\Hdmpi\Capturer;

use Asmblah\Hdmpi\Capturer\Spec\CapturerSpecInterface;
use Asmblah\Hdmpi\Session\SessionInterface;

/**
 * Interface CapturerFactoryInterface.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface CapturerFactoryInterface
{
    public function createCapturer(
        CapturerSpecInterface $capturerSpec,
        SessionInterface $session
    );
}
