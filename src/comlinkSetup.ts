/*
 * KVM & HDMI capture with TypeScript and PHP using Uniter.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/hdmpi
 *
 * Released under the MIT license
 * https://github.com/asmblah/hdmpi/raw/master/MIT-LICENSE.txt
 */

/**
 * Note: this setup should be moved to a Uniter plugin.
 */

import { TransferHandler, transferHandlers } from 'comlink';

const PROXY_TRANSFER_HANDLER = 'proxy';
const UNITER_TRANSFER_HANDLER = 'uniter.proxy_classes';

if (!transferHandlers.has(PROXY_TRANSFER_HANDLER)) {
    throw new Error(
        `Comlink "${PROXY_TRANSFER_HANDLER}" transfer handler is not defined`
    );
}

const proxyTransferHandler = transferHandlers.get(
    PROXY_TRANSFER_HANDLER
) as TransferHandler<any, any>;

transferHandlers.set(UNITER_TRANSFER_HANDLER, {
    canHandle: (obj: object) =>
        // FIXME: Needs to look up in Uniter's ValueStorage as that stores data for all valid NativeProxy instances (?)
        obj &&
        typeof obj === 'object' &&
        '__construct' in Object.getPrototypeOf(obj),
    serialize: (uniterProxy: any) => {
        const prototype = Object.getPrototypeOf(uniterProxy);
        const propertyNames = Object.keys(prototype);
        const transferables: unknown[] = [];

        console.log(propertyNames);

        const serialisedUniterProxy = [];

        for (const methodName of propertyNames) {
            const serialisedMethodProxy = proxyTransferHandler.serialize(
                function (...args: any[]) {
                    console.log(`Calling ${methodName}() ...`);

                    return prototype[methodName].apply(uniterProxy, args);
                }
            );

            serialisedUniterProxy.push({
                methodName,
                // Extract the proxy MessageChannel port.
                func: serialisedMethodProxy[0],
            });

            // Add any transferables (should be the MessageChannel port) to the top-level list.
            transferables.push(...serialisedMethodProxy[1]);
        }

        return [serialisedUniterProxy, transferables];
    },
    deserialize: (serialisedUniterProxy: any): object => {
        const proxy: any = {};

        for (const method of serialisedUniterProxy) {
            /*
             * Deserialise the proxy MessageChannel port. Note that on the receiving side,
             * the transferables list is not relevant as that will have been handled
             * by the host environment by this point.
             */
            proxy[method.methodName] = proxyTransferHandler.deserialize(
                method.func
            );
        }

        return proxy;
    },
} as unknown as TransferHandler<any, any>);
