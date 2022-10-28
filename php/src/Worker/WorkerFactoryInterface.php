<?php

/**
 * KVM & HDMI capture with TypeScript and PHP using Uniter.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/hdmpi
 *
 * Released under the MIT license
 * https://github.com/asmblah/hdmpi/raw/master/MIT-LICENSE.txt
 */

namespace Asmblah\Hdmpi\Worker;

use Asmblah\Hdmpi\Worker\Spec\WorkerSpecInterface;

/**
 * Interface WorkerFactoryInterface.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface WorkerFactoryInterface
{
    /**
     * Registers a factory for workers of the given spec type.
     *
     * @param string $specFqcn
     * @param WorkerSubFactoryInterface $workerFactory
     */
    public function registerFactory(
        $specFqcn,
        WorkerSubFactoryInterface $workerFactory
    );

    /**
     * Creates a worker from the given spec.
     *
     * @param WorkerSpecInterface $workerSpec
     * @return WorkerInterface
     */
    public function createWorker(WorkerSpecInterface $workerSpec);
}
