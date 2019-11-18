<?php

namespace SunValley\TaskManager\Symfony\Task;

use SunValley\TaskManager\ProgressReporter;
use SunValley\TaskManager\Task\AbstractTask;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AbstractSymfonyTask defines a task that can be used with the framework. This task creates a Kernel each time a
 * task is run and destroys it at the end of the task.
 *
 * @package SunValley\TaskManager\Symfony\Task
 */
abstract class AbstractSymfonyTask extends AbstractTask
{

    /** @inheritDoc */
    final public function run(ProgressReporter $progressReporter): void
    {
        
        $kernel = TaskEnvironment::generateKernelFromEnv();
        $kernel->boot();
        $this->__run($progressReporter, $kernel->getContainer());
        $kernel->shutdown();

    }

    /**
     * This method should not store anything from container to somewhere else to avoid memory leaks.
     *
     * @param ProgressReporter   $reporter
     * @param ContainerInterface $container
     */
    abstract protected function __run(ProgressReporter $reporter, ContainerInterface $container): void;


}