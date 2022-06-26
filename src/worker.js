/*
 * KVM & HDMI capture with TypeScript and PHP using Uniter.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/hdmpi
 *
 * Released under the MIT license
 * https://github.com/asmblah/hdmpi/raw/master/MIT-LICENSE.txt
 */

// Worker entrypoint, ensures worker code is transpiled by TS-Node.

const Comlink = require('comlink');
const nodeEndpoint = require('comlink/dist/umd/node-adapter');
const parentPort = require('node:worker_threads').parentPort;

require('ts-node/register/transpile-only');

require('./comlinkSetup');
const createHdmpi = require('./index.ts').default;

// Library entrypoint.
Comlink.expose({ createHdmpi }, nodeEndpoint(parentPort));
