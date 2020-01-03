<?php

namespace SunValley\TaskManager\Symfony\Task;

use React\EventLoop\LoopInterface;
use SunValley\TaskManager\Configuration as PoolConfiguration;
use SunValley\TaskManager\TaskManager;
use SunValley\TaskManager\TaskManagerFactoryInterface;
use SunValley\TaskManager\TaskQueueInterface;
use SunValley\TaskManager\TaskStorageInterface;

/**
 * Class TaskManagerFactory is a singleton factory to provide a TaskManager to the caller and also returns a single
 * task manager..
 *
 * @package SunValley\TaskManager\Symfony\Task
 */
class TaskManagerFactory implements TaskManagerFactoryInterface
{

    /** @var LoopInterface */
    protected $loop;

    /** @var TaskQueueInterface */
    protected $queue;

    /** @var PoolConfiguration|null */
    protected $configuration;

    /** @var TaskStorageInterface|null */
    protected $storage;

    /** @var TaskManager */
    protected $cachedManager;

    /** @var TaskEnvironmentInterface */
    protected $environment;

    public function __construct(
        LoopInterface $loop,
        TaskQueueInterface $queue,
        TaskEnvironmentInterface $environment,
        ?PoolConfiguration $configuration = null,
        ?TaskStorageInterface $storage = null
    ) {
        $this->loop          = $loop;
        $this->queue         = $queue;
        $this->configuration = $configuration;
        $this->storage       = $storage;
        $this->environment   = $environment;
    }

    /**
     * If required creates the task manager and returns it.
     *
     * @param PoolConfiguration|null $configuration Providing a configuration will create a fresh manager. Generated
     *                                              manager this way is not cached by this factory.
     *
     * @return TaskManager
     */
    public function generate(?PoolConfiguration $configuration = null): TaskManager
    {
        if ($configuration === null && $this->cachedManager !== null) {
            return $this->cachedManager;
        }

        $this->environment->register();

        if ($configuration === null && $this->configuration === null) {
            throw new \RuntimeException('A configuration should be provided if no default configuration exists!');
        }

        $taskManager = new TaskManager(
            $this->loop, $this->queue, $configuration ?? $this->configuration, $this->storage
        );

        if ($configuration !== null) {
            return $taskManager;
        }

        return $this->cachedManager = $taskManager;
    }

    /** @return LoopInterface */
    public function getLoop(): LoopInterface
    {
        return $this->loop;
    }

}