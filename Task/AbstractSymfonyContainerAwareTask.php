<?php

namespace SunValley\TaskManager\Symfony\Task;

use SunValley\TaskManager\ProgressReporter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class AbstractSymfonyTask defines a task that can be used with the framework. This task creates a Kernel each time a
 * task is run and destroys it at the end of the task.
 *
 * @package SunValley\TaskManager\Symfony\Task
 */
abstract class AbstractSymfonyContainerAwareTask extends AbstractSymfonyTask
{

    protected function runWithInitializedKernel(ProgressReporter $progressReporter, Kernel $kernel)
    {
        return $this->__run($progressReporter, $kernel->getContainer());
    }

    abstract protected function __run(ProgressReporter $progressReporter, ContainerInterface $container);


}