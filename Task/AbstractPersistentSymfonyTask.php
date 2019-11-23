<?php


namespace SunValley\TaskManager\Symfony\Task;


use SunValley\TaskManager\ProgressReporter;
use SunValley\TaskManager\Task\AbstractTask;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class AbstractPersistentSymfonyTask defines a task that can be used with the framework.
 *
 * This task creates a kernel for the first time a task is received. It never shutdowns the kernel. The kernel is also
 * accessible from protected static $kernel variable.
 *
 * @package SunValley\TaskManager\Symfony\Task
 */
abstract class AbstractPersistentSymfonyTask extends AbstractTask
{

    /** @var Kernel */
    protected static $kernel;

    /** @inheritDoc */
    final public function run(ProgressReporter $progressReporter): void
    {
        if (static::$kernel === null) {
            static::$kernel = $kernel = TaskEnvironment::generateKernelFromEnv();
        } else {
            $kernel = static::$kernel;
        }

        $kernel->boot();
        $result = $this->__run($progressReporter, $kernel->getContainer());
        if (!$progressReporter->isCompleted() && !$progressReporter->isFailed()) {
            $progressReporter->finishTask($result);
        }

        $kernel->shutdown();
    }

    /**
     * This method should not store anything from container to somewhere else to avoid memory leaks.
     *
     * @param ProgressReporter   $reporter
     * @param ContainerInterface $container
     *
     * @return mixed The return value will be set as result to this task
     */
    abstract protected function __run(ProgressReporter $reporter, ContainerInterface $container);

}