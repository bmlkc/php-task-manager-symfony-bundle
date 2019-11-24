<?php

namespace SunValley\TaskManager\Symfony\Task;

use React\EventLoop\LoopInterface;
use SunValley\TaskManager\Configuration;
use SunValley\TaskManager\Configuration as PoolConfiguration;
use SunValley\TaskManager\TaskManager;
use SunValley\TaskManager\TaskQueueInterface;
use SunValley\TaskManager\TaskStorageInterface;

/**
 * Class TaskManagerFactory is a singleton factory to provide a TaskManager to the caller and also returns a single
 * task manager..
 *
 * @package SunValley\TaskManager\Symfony\Task
 */
class TaskManagerFactory
{

    /** @var LoopInterface */
    protected $loop;

    /** @var TaskQueueInterface */
    protected $queue;

    /** @var PoolConfiguration */
    protected $configuration;

    /** @var TaskStorageInterface */
    protected $storage;

    /** @var TaskManager */
    protected $cachedManager;

    /** @var TaskEnvironmentInterface */
    protected $environment;

    public function __construct(
        LoopInterface $loop,
        TaskQueueInterface $queue,
        Configuration $configuration,
        TaskEnvironmentInterface $environment,
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
     * @return TaskManager
     */
    public function generate(): TaskManager
    {
        if ($this->cachedManager !== null) {
            return $this->cachedManager;
        }

        $this->environment->register();

        return $this->cachedManager = new TaskManager($this->loop, $this->queue, $this->configuration, $this->storage);
    }

    /** @return LoopInterface */
    public function getLoop(): LoopInterface
    {
        return $this->loop;
    }

}