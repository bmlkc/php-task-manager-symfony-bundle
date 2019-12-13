<?php

namespace SunValley\TaskManager\Symfony\Task;

use SunValley\TaskManager\ProgressReporter;
use SunValley\TaskManager\Task\AbstractTask;
use Symfony\Component\HttpKernel\Kernel;

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

        if ($this->isPersistentTask()) {
            $kernel = self::getPersistentKernel();
        } else {
            $kernel = TaskEnvironment::generateKernelFromEnv();
        }

        $kernel->boot();
        $result = $this->runWithInitializedKernel($progressReporter, $kernel);

        if (!$progressReporter->isCompleted() && !$progressReporter->isFailed()) {
            $progressReporter->finishTask($result);
        }

        $kernel->shutdown();

    }

    abstract protected function runWithInitializedKernel(ProgressReporter $progressReporter, Kernel $kernel);

    final private function isPersistentTask(): bool
    {
        return property_exists(static::class, 'kernel');
    }

    /**
     * @return Kernel
     */
    final private static function getPersistentKernel(): Kernel
    {
        /** @noinspection PhpUndefinedFieldInspection */
        if (static::$kernel === null) {
            /** @noinspection PhpUndefinedFieldInspection */
            static::$kernel = $kernel = TaskEnvironment::generateKernelFromEnv();
        } else {
            /** @noinspection PhpUndefinedFieldInspection */
            $kernel = static::$kernel;
        }

        return $kernel;
    }


}