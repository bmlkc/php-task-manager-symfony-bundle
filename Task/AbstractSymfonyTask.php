<?php

namespace SunValley\TaskManager\Symfony\Task;

use SunValley\TaskManager\ProgressReporter;
use SunValley\TaskManager\Task\AbstractTask;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class AbstractSymfonyTask defines a task that can be used with the framework.
 *
 * Depending on whether this task is "persistent" or not, this task might:
 *
 * 1) create a Kernel each time a task is run and destroy it at the end of this
 *    particular task execution (non-persistent task).
 * 2) create a kernel and never shut it down
 *    in order to reuse it later (persistent task)
 *
 * a task class becomes "persistent" by using PersistentTaskTrait
 *
 * @package SunValley\TaskManager\Symfony\Task
 */
abstract class AbstractSymfonyTask extends AbstractTask
{

    /** @inheritDoc */
    final public function run(ProgressReporter $progressReporter): void
    {

        if ($this->isPersistentTask()) {
            /** @noinspection PhpUndefinedMethodInspection */
            $kernel = static::getPersistentKernel(); // this method is supposed to be provided by PersistentTaskTrait
        } else {
            $kernel = TaskEnvironment::generateKernelFromEnv();
        }

        $kernel->boot();

        $result = $this->runWithInitializedKernel($progressReporter, $kernel);

        if (!$progressReporter->isCompleted() && !$progressReporter->isFailed()) {
            $progressReporter->finishTask($result);
        }

        if (!$this->isPersistentTask()) {
            $kernel->shutdown();
        }
    }

    abstract protected function runWithInitializedKernel(ProgressReporter $progressReporter, Kernel $kernel);

    final private function isPersistentTask(): bool
    {
        return method_exists(static::class, 'getPersistentKernel');
    }


}