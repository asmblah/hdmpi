/**
 * KVM & HDMI capture with TypeScript and PHP using Uniter.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/hdmpi
 *
 * Released under the MIT license
 * https://github.com/asmblah/hdmpi/raw/master/MIT-LICENSE.txt
 */

import { wrap } from 'comlink';
import nodeEndpoint from 'comlink/dist/umd/node-adapter';
import { Worker } from 'node:worker_threads';

interface WorkerInterface {
    createHdmpi(): Promise<HdmpiInterface>;
}

interface HdmpiInterface {
    createAudioForwarderClient(
        localHost: string,
        multicastGroup: string,
        audioPort: number,
        audioFifoPath: string
    ): Promise<AudioForwarderClientInterface>;

    createAudioServer(
        localHost: string,
        multicastGroup: string,
        audioPort: number,
        audioFifoPath: string
    ): Promise<AudioServerInterface>;

    createReceiverHeartbeatCapturerClient(
        localHost: string,
        heartbeatPort: number
    ): Promise<ReceiverHeartbeatCapturerClientInterface>;

    createTransmitterHeartbeatSenderClient(
        broadcastAddress: string,
        heartbeatPort: number
    ): Promise<TransmitterHeartbeatSenderClientInterface>;

    createVideoForwarderClient(
        localHost: string,
        multicastGroup: string,
        videoPort: number,
        videoSyncPort: number,
        videoFifoPath: string
    ): Promise<VideoForwarderClientInterface>;

    createVideoServer(
        localHost: string,
        multicastGroup: string,
        videoPort: number,
        videoSyncPort: number,
        videoFifoPath: string
    ): Promise<VideoServerInterface>;
}

interface AudioForwarderClientInterface {
    start(): Promise<void>;
}

interface AudioServerInterface {
    start(): Promise<void>;
}

interface ReceiverHeartbeatCapturerClientInterface {
    start(): Promise<void>;
}

interface TransmitterHeartbeatSenderClientInterface {
    start(): Promise<void>;
}

interface VideoForwarderClientInterface {
    start(): Promise<void>;
}

interface VideoServerInterface {
    start(): Promise<void>;
}

const createWorker = () => {
    return wrap<WorkerInterface>(
        nodeEndpoint(new Worker(__dirname + '/worker.js'))
    );
};

export { createWorker };
