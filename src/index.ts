/*
 * KVM & HDMI capture with TypeScript and PHP using Uniter.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/hdmpi
 *
 * Released under the MIT license
 * https://github.com/asmblah/hdmpi/raw/master/MIT-LICENSE.txt
 */

import { createSocket } from 'node:dgram';
import {
    createReadStream as fsCreateReadStream,
    createWriteStream as fsCreateWriteStream,
    WriteStream,
} from 'node:fs';
import Exception from './Exception/Exception';
import { RemoteInfo, Socket } from 'dgram';

export default async () => {
    await require('dotphp/register')();

    const entryModule = require(__dirname + '/../php/src/entry.php');
    const entryEngine = entryModule();
    const entry = (await entryEngine.execute()).getNative();

    const writeToWriteStream = (writeStream: WriteStream, data: Buffer) => {
        return entryEngine.createFFIResult(
            () => {
                throw new Exception('Async mode expected');
            },
            () => {
                return new Promise((resolve, reject) => {
                    writeStream.write(
                        data,
                        (error: Error | null | undefined) => {
                            if (error) {
                                reject(error);
                                return;
                            }

                            resolve(null);
                        }
                    );
                });
            }
        );
    };

    const onSocketMessage = (
        socket: Socket,
        callback: (msg: Buffer, rinfo: RemoteInfo) => Promise<unknown>
    ) => {
        type MessageEvent = { msg: Buffer; rinfo: RemoteInfo };
        let dequeuing = false;
        const eventQueue: MessageEvent[] = [];

        const dequeueEvent = () => {
            dequeuing = true;

            const { msg, rinfo } = eventQueue.shift() as MessageEvent;

            callback(msg, rinfo).finally(() => {
                dequeuing = false;

                if (eventQueue.length > 0) {
                    // TODO: requestIdleCallback to allow sockets to drain etc.?
                    dequeueEvent();
                }
            });
        };

        socket.on('message', (msg, rinfo) => {
            eventQueue.push({ msg, rinfo });

            if (!dequeuing) {
                dequeueEvent();
            }
        });
    };

    const sendToSocket = (
        socket: Socket,
        datagram: Buffer,
        port: number,
        address: string
    ) => {
        return entryEngine.createFFIResult(
            () => {
                throw new Exception('Async mode expected');
            },
            () => {
                return new Promise((resolve, reject) => {
                    socket.send(datagram, port, address, (error) => {
                        if (error) {
                            console.log('SEND FAILED: ' + error);

                            reject(error);
                            return;
                        }

                        resolve(null);
                    });
                });
            }
        );
    };

    const bindSocket = (socket: Socket, port: number, address: string) => {
        return entryEngine.createFFIResult(
            () => {
                throw new Exception('Async mode expected');
            },
            () => {
                return new Promise((resolve) => {
                    socket.bind(port, address, () => {
                        resolve(null);
                    });
                });
            }
        );
    };

    return await entry(
        setTimeout,
        createSocket,
        Buffer,
        fsCreateReadStream,
        fsCreateWriteStream,
        writeToWriteStream,
        bindSocket,
        onSocketMessage,
        sendToSocket
    );
};
