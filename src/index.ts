/*
 * KVM & HDMI capture with TypeScript and PHP using Uniter.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/hdmpi
 *
 * Released under the MIT license
 * https://github.com/asmblah/hdmpi/raw/master/MIT-LICENSE.txt
 */

import { createSocket } from 'dgram';
import { createWriteStream as fsCreateWriteStream, WriteStream } from 'fs';
import Exception from './Exception/Exception';

export default async () => {
    await require('dotphp/register')();

    const serverEntryModule = require(__dirname +
        '/../php/src/server_entry.php');
    const serverEngine = serverEntryModule();
    const serverEntry = (await serverEngine.execute()).getNative();

    return await serverEntry(
        createSocket,
        Buffer,
        fsCreateWriteStream,
        (writeStream: WriteStream, data: Buffer) => {
            return serverEngine.createFFIResult(
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
        }
    );
};
