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
    const localHost = '192.168.168.1';
    const multicastGroup = '226.2.2.2';
    const videoPort = 2068;
    const videoSyncPort = 2067;
    const videoFifoPath = '/var/run/hdmpi_video_fifo';
    const audioPort = 2066;
    const audioFifoPath = '/var/run/hdmpi_audio_fifo';

    const videoWorker = createWorker();
    const videoServer = await (
        await videoWorker.createHdmpi()
    ).createVideoServer(
        localHost,
        multicastGroup,
        videoPort,
        videoSyncPort,
        videoFifoPath
    );

    const audioWorker = createWorker();
    const audioServer = await (
        await audioWorker.createHdmpi()
    ).createAudioServer(localHost, multicastGroup, audioPort, audioFifoPath);

    console.log('Server starting...');

    await videoServer.start();
    await audioServer.start();

    console.log('Server started.');
})().catch((error) => {
    console.log('ERROR: ' + error.stack);
});
