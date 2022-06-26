#!/usr/bin/env node

/**
 * KVM & HDMI capture with TypeScript and PHP using Uniter.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/hdmpi
 *
 * Released under the MIT license
 * https://github.com/asmblah/hdmpi/raw/master/MIT-LICENSE.txt
 */

/**
 * JavaScript server entry script stub. Registers ts-node before delegating
 * to the TypeScript entrypoint, so that ts-node does not need
 * to be available in $PATH.
 */

require('ts-node/register/transpile-only');
require('./server');
