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
 * Interface LoggerInterface.
 *
 * TODO: Replace with PSR logger interface.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface LoggerInterface
{
    public function debug($message);

    public function error($message);

    public function info($message);

    public function warn($message);
}
