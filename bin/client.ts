#!/usr/bin/env ts-node

/**
 * KVM & HDMI capture with TypeScript and PHP using Uniter.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/hdmpi
 *
 * Released under the MIT license
 * https://github.com/asmblah/hdmpi/raw/master/MIT-LICENSE.txt
 */

import '../src/comlinkSetup';
import { createWorker } from '../src/control';

(async () => {
    const localHost = '192.168.168.55';
    const broadcastAddress = '192.168.168.255';
    const multicastGroup = '226.2.2.2';
    const heartbeatPort = 48689;
    const videoPort = 2068;
    const videoSyncPort = 2067;
    const videoFifoPath = '/var/run/hdmpi_video_fifo';
    const audioPort = 2066;
    const audioFifoPath = '/var/run/hdmpi_audio_fifo';

    const audioForwarderWorker = createWorker();
    const audioForwarderClient = await (
        await audioForwarderWorker.createHdmpi()
    ).createAudioForwarderClient(
        localHost,
        multicastGroup,
        audioPort,
        audioFifoPath
    );

    const receiverHeartbeatCapturerWorker = createWorker();
    const receiverHeartbeatCapturer = await (
        await receiverHeartbeatCapturerWorker.createHdmpi()
    ).createReceiverHeartbeatCapturerClient(localHost, heartbeatPort);

    const transmitterHeartbeatSenderWorker = createWorker();
    const transmitterHeartbeatSender = await (
        await transmitterHeartbeatSenderWorker.createHdmpi()
    ).createTransmitterHeartbeatSenderClient(broadcastAddress, heartbeatPort);

    const videoForwarderWorker = createWorker();
    const videoForwarderClient = await (
        await videoForwarderWorker.createHdmpi()
    ).createVideoForwarderClient(
        localHost,
        multicastGroup,
        videoPort,
        videoSyncPort,
        videoFifoPath
    );

    console.log('Client starting...');

    await audioForwarderClient.start();
    // await receiverHeartbeatCapturer.start(); // FIXME: Duplicate port binding!
    await transmitterHeartbeatSender.start();
    await videoForwarderClient.start();

    console.log('Client started.');
})().catch((error) => {
    console.log('ERROR: ' + error.stack);
});
