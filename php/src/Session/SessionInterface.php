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
 * Interface SessionInterface.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface SessionInterface
{
    /**
     * Starts capturing datagrams for this session.
     *
     * @param callable $onDatagram
     */
    public function capture(callable $onDatagram);

    /**
     * Fetches the port.
     *
     * @return int
     */
    public function getPort();

    /**
     * Sends a datagram for this session.
     *
     * @param object $datagram
     */
    public function send($datagram);

    /**
     * Starts the session.
     */
    public function start();
}
