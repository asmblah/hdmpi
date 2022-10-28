<?php

/**
 * KVM & HDMI capture with TypeScript and PHP using Uniter.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/hdmpi
 *
 * Released under the MIT license
 * https://github.com/asmblah/hdmpi/raw/master/MIT-LICENSE.txt
 */

namespace Asmblah\Hdmpi\Session;

/**
 * Interface SessionFactoryInterface.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface SessionFactoryInterface
{
    /**
     * Creates a new BroadcastSession.
     *
     * @param string $broadcastAddress
     * @param number $port
     * @return BroadcastSessionInterface
     */
    public function createBroadcastSession(
        $broadcastAddress,
        $port
    );

    /**
     * Creates a new MulticastSession.
     *
     * @param string $localHost
     * @param string $boundAddress
     * @param string $multicastGroup
     * @param number $port
     * @return MulticastSessionInterface
     */
    public function createMulticastSession(
        $localHost,
        $boundAddress,
        $multicastGroup,
        $port
    );

    /**
     * Creates a new UnicastSession.
     *
     * @param string $address
     * @param number $port
     * @return UnicastSessionInterface
     */
    public function createUnicastSession($address, $port);
}
