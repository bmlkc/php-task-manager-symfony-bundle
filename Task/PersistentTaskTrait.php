<?php


namespace SunValley\TaskManager\Symfony\Task;

use Symfony\Component\HttpKernel\Kernel;

/**
 * Trait PersistentTaskTrait
 *
  *
 * @package SunValley\TaskManager\Symfony\Task
 */
trait PersistentTaskTrait
{
    /** @var Kernel */
    protected static $kernel;

    /**
     * @return Kernel
     */
    final protected static function getPersistentKernel(): Kernel
    {
        if (self::$kernel === null) {
            self::$kernel = $kernel = TaskEnvironment::generateKernelFromEnv();
        } else {
            $kernel = static::$kernel;
        }

        return $kernel;
    }
}