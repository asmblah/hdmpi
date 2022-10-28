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
use RuntimeException;

/**
 * Class WorkerFactory.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class WorkerFactory implements WorkerFactoryInterface
{
    /**
     * @var WorkerSubFactoryInterface[]
     */
    private $subFactories = [];

    /**
     * @inheritDoc
     */
    public function registerFactory(
        $specFqcn,
        WorkerSubFactoryInterface $workerFactory
    ) {
        $this->subFactories[$specFqcn] = $workerFactory;
    }

    /**
     * @inheritDoc
     */
    public function createWorker(WorkerSpecInterface $workerSpec)
    {
        $specFqcn = $workerSpec::class;

        if (!array_key_exists($specFqcn, $this->subFactories)) {
            throw new RuntimeException(
                sprintf(
                    'No factory registered for spec type "%s"',
                    $specFqcn
                )
            );
        }

        $factoryCallable = $this->subFactories[$specFqcn]->getFactoryCallable();

        return $factoryCallable($workerSpec);
    }
}
