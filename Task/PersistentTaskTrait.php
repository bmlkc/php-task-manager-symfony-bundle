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
}