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

use Asmblah\Hdmpi\Logger\LoggerInterface;
use SplQueue;

/**
 * Class MulticastClientSession.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class MulticastClientSession implements SessionInterface
{
    /**
     * @var string
     */
    private $localHost;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var int
     */
    private $maxMessageBacklog;
    /**
     * @var string
     */
    private $multicastGroup;
    /**
     * @var object
     */
    private $nativeUdp4Socket;
    /**
     * @var int
     */
    private $port;
    /**
     * @var int
     */
    private $receiveBufferSizeBytes;

    /**
     * @param LoggerInterface $logger
     * @param object $nativeUdp4Socket
     * @param string $localHost
     * @param string $multicastGroup
     * @param int $port
     * @param int $maxMessageBacklog
     * @param int $receiveBufferSizeBytes
     */
    public function __construct(
        LoggerInterface $logger,
        $nativeUdp4Socket,
        $localHost,
        $multicastGroup,
        $port,
        $maxMessageBacklog,
        $receiveBufferSizeBytes
    ) {
        $this->localHost = $localHost;
        $this->logger = $logger;
        $this->maxMessageBacklog = $maxMessageBacklog;
        $this->multicastGroup = $multicastGroup;
        $this->nativeUdp4Socket = $nativeUdp4Socket;
        $this->port = $port;
        $this->receiveBufferSizeBytes = $receiveBufferSizeBytes;
    }

    /**
     * @inheritDoc
     */
    public function capture(callable $onPacket)
    {
        $logger = $this->logger;
        $nativeUdp4Socket = $this->nativeUdp4Socket;

        $localHost = $this->localHost;
        $multicastGroup = $this->multicastGroup;
        $maxMessageBacklog = $this->maxMessageBacklog;
        $receiveBufferSizeBytes = $this->receiveBufferSizeBytes;
        $messageQueue = new SplQueue();
        $busy = false;

        $nativeUdp4Socket->on('listening', function () use (
            $localHost,
            $logger,
            $multicastGroup,
            $nativeUdp4Socket,
            $receiveBufferSizeBytes
        ) {
            $address = $nativeUdp4Socket->address();

//            $nativeUdp4Socket->setBroadcast(true);
            $nativeUdp4Socket->setMulticastTTL(128);
            $nativeUdp4Socket->addMembership(/*'192.168.168.55', */$multicastGroup, $localHost);

            $logger->info(
                'UDP Client listening on ' . $address['address'] . ':' . $address['port'] . ', multicast group ' . $multicastGroup
            );

//            $nativeUdp4Socket->setRecvBufferSize($receiveBufferSizeBytes);
//            $actualReceiveBufferSizeBytes = $nativeUdp4Socket->getRecvBufferSize();
//
//            if ($actualReceiveBufferSizeBytes < $receiveBufferSizeBytes) {
//                // For when "net.core.rmem_default" and "net.core.rmem_max" are set too low.
//                $logger->error(
//                    sprintf(
//                        'Failed to resize receive buffer, target %d bytes, actual %d bytes',
//                        $receiveBufferSizeBytes,
//                        $actualReceiveBufferSizeBytes
//                    )
//                );
//            }
//
//            $logger->info('Receive buffer size: ' . $nativeUdp4Socket->getRecvBufferSize() . ' bytes');
        });

        $nativeUdp4Socket->on('message', function ($message, $remoteInfo) use (
            &$busy,
            $logger,
            $maxMessageBacklog,
            $messageQueue,
            $onPacket
        ) {
            // NB: This function will return a Promise to JS-land.

            if ($busy) {
//                $logger->debug('Busy, discarding chunk');
                return;
            }

            $busy = true;
            $onPacket($message, $remoteInfo);
            $busy = false;
        });

        $nativeUdp4Socket->on('error', function ($error) use ($logger) {
            $logger->error('UDP socket error: ' . $error);
        });

        /*
         * Note that different to documentation in various places, the host here must be
         * the multicast group and not the local host
         * (thanks to https://stackoverflow.com/questions/45044189/node-js-multicast-client).
         */
        $nativeUdp4Socket->bind($this->port, $this->multicastGroup);
    }

    // /**
    //     * @inheritDoc
    //     */
    //    public function capture(callable $onPacket)
    //    {
    //        $logger = $this->logger;
    //        $nativeUdp4Socket = $this->nativeUdp4Socket;
    //
    //        $localHost = $this->localHost;
    //        $multicastGroup = $this->multicastGroup;
    //        $maxMessageBacklog = $this->maxMessageBacklog;
    //        $receiveBufferSizeBytes = $this->receiveBufferSizeBytes;
    //        $messageQueue = new SplQueue();
    //        $busy = false;
    //
    //        $nativeUdp4Socket->on('listening', function () use (
    //            $localHost,
    //            $logger,
    //            $multicastGroup,
    //            $nativeUdp4Socket,
    //            $receiveBufferSizeBytes
    //        ) {
    //            $address = $nativeUdp4Socket->address();
    //
    ////            $nativeUdp4Socket->setBroadcast(true);
    //            $nativeUdp4Socket->setMulticastTTL(128);
    //            $nativeUdp4Socket->addMembership(/*'192.168.168.55', */$multicastGroup, $localHost);
    //
    //            $logger->info(
    //                'UDP Client listening on ' . $address['address'] . ':' . $address['port'] . ', multicast group ' . $multicastGroup
    //            );
    //
    //            $nativeUdp4Socket->setRecvBufferSize($receiveBufferSizeBytes);
    //            $actualReceiveBufferSizeBytes = $nativeUdp4Socket->getRecvBufferSize();
    //
    //            if ($actualReceiveBufferSizeBytes < $receiveBufferSizeBytes) {
    //                // For when "net.core.rmem_default" and "net.core.rmem_max" are set too low.
    //                $logger->error(
    //                    sprintf(
    //                        'Failed to resize receive buffer, target %d bytes, actual %d bytes',
    //                        $receiveBufferSizeBytes,
    //                        $actualReceiveBufferSizeBytes
    //                    )
    //                );
    //            }
    //
    //            $logger->info('Receive buffer size: ' . $nativeUdp4Socket->getRecvBufferSize() . ' bytes');
    //        });
    //
    //        $nativeUdp4Socket->on('message', function ($message, $remoteInfo) use (
    //            &$busy,
    //            $logger,
    //            $maxMessageBacklog,
    //            $messageQueue,
    //            $onPacket
    //        ) {
    //            // NB: This function will return a Promise to JS-land.
    //
    //            if ($busy) {
    //                $messageQueue->enqueue(['message' => $message, 'remoteInfo' => $remoteInfo]);
    //
    //                $messageCount = count($messageQueue);
    //
    //                if ($messageCount > $maxMessageBacklog) {
    ////                    $logger->debug('Backlog full, discarding oldest queued message');
    //
    //                    $messageQueue->dequeue();
    //                } else {
    ////                    $logger->debug('Queued message, count is now: ' . $messageCount);
    //                }
    //
    //                return;
    //            }
    //
    //            $busy = true;
    //            $onPacket($message, $remoteInfo);
    //
    //            while (!$messageQueue->isEmpty()) {
    //                $queuedMessage = $messageQueue->dequeue();
    //
    //                $onPacket($queuedMessage['message'], $queuedMessage['remoteInfo']);
    //            }
    //
    //            $busy = false;
    //
    //
    //
    ////            $messageQueue->enqueue(['message' => $message, 'remoteInfo' => $remoteInfo]);
    ////
    ////            $messageCount = count($messageQueue);
    ////
    ////            if ($messageCount > $maxMessageBacklog) {
    ////                $logger->error/*debug*/('Backlog full, discarding oldest queued message');
    ////
    ////                $messageQueue->dequeue();
    ////            } else {
    ////                $logger->debug('Queued message, count is now: ' . $messageCount);
    ////            }
    //        });
    //
    //        $nativeUdp4Socket->on('error', function ($error) use ($logger) {
    //            $logger->error('UDP socket error: ' . $error);
    //        });
    //
    //        /*
    //         * Note that different to documentation in various places, the host here must be
    //         * the multicast group and not the local host
    //         * (thanks to https://stackoverflow.com/questions/45044189/node-js-multicast-client).
    //         */
    //        $nativeUdp4Socket->bind($this->port, $this->multicastGroup);
    //
    ////        while (true) {
    ////            if ($messageQueue->isEmpty()) {
    ////                usleep(1 * 1000);
    ////                continue;
    ////            }
    ////
    ////            // TODO: Keep a count of # msgs processed sync/sequentially,
    ////            //       and sleep if it exceeds a max to avoid hogging CPU & blocking event loop?
    ////
    ////            $this->logger->debug('Ready for packet');
    ////
    ////            $message = $messageQueue->dequeue();
    ////
    ////            $onPacket($message['message'], $message['remoteInfo']);
    ////        }
    //    }
}
