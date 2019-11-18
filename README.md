# PHP Task Manager - Symfony Bundle

[See main repository for base usage](https://github.com/sunvalley-technologies/php-task-manager)

This bundle integrates the task manager to Symfony. Supports Symfony ^3.0 and ^4.0.

## Installing

``
composer require sunvalley-technologies/php-task-manager-symfony-bundle
``

Make sure to add the bundle to your Kernel's bundles.

## Configuration

Complete configuration looks like following:

````yaml
php_task_manager:
    task_queue: redis://127.0.0.1:6379
    task_storage: redis://127.0.0.1:6379
    pool:
        minimum_processes: 1
        maximum_processes: 10
        time_to_live: 60
        max_jobs_per_process: 10
````

From which `task_queue` is the only required configuration parameter.

`task_storage` is optional and if given is used to store task information. 

If you have a non-standard Kernel have a look and replace the `SunValley\TaskManager\Symfony\Task\TaskEnvironment` class 
and `AbstractSymfonyTask` to provide a proper Kernel for your tasks.

## Generating and Submitting Tasks

It is necessary to generate tasks to control exactly what each task is doing. This can be thought like generating controllers.

All kernel dependent classes should extend `AbstractSymfonyTask` and they are synchronous tasks by default.

Here is a sample task that persist a doctrine entity:

````php
<?php

class ExampleTask extends \SunValley\TaskManager\Symfony\Task\AbstractSymfonyTask {
    


    protected function __run(\SunValley\TaskManager\ProgressReporter $reporter,\Symfony\Component\DependencyInjection\ContainerInterface $container) : void{
        $data = $this->getOptions()['data'];
        $entityManager = $container->get('entity_manager');
        $entity = $entityManager->find('\Some\Entity');
        $entity->setData($data);
        $entityManager->persist($entity);
        $entityManager->flush();

        $reporter->finishTask();
    }

    public function buildOptionsResolver() : \Symfony\Component\OptionsResolver\OptionsResolver{
        $resolver = new Symfony\Component\OptionsResolver\OptionsResolver();
        $resolver->setRequired('data');
        return $resolver;
    }

}

class MyService {
    use \Symfony\Component\DependencyInjection\ContainerAwareTrait;

    public function changeDataOnBackground($data) {
        $task = new ExampleTask(uniqid('', true), ['data' => $data]); // this can throw an exception if options are invalid
        $this->container->get('php_task_manager_client')->submitTaskSync($task);
    }
}

class MyAsyncService {

    /** @var \SunValley\TaskManager\Symfony\Task\TaskManagerFactory */
    private $taskManagerFactory;

    public function __construct($taskManagerFactory) { $this->taskManagerFactory = $taskManagerFactory; }

    public function changeDataOnBackground($data): \React\Promise\PromiseInterface {
        $task = new ExampleTask(uniqid('', true), ['data' => $data]); // this can throw an exception if options are invalid

        return $this->taskManagerFactory->generate()->submitTask($task);
    }
}

````

The call to `finishTask` or `failTask` is mandatory. Any exception that is thrown however is caught and reported back with `failTask($error)`.

To submit a task, the task should be constructed and then it can be submitted with the client as on the example service above.

Task constructors should never block otherwise the main task manager loop can also get blocked.

## Starting task manager

Console command `task:manager` can be used to start the task manager.

In order to attach to your running loops that you also started with Symfony there is a factory for task manager `SunValley\TaskManager\Symfony\Task\TaskManagerFactory`.

Task manager uses the loop `php_task_manager_loop` defined in its configuration by default.

Many of these can be changed with a compiler pass in case a customization is necessary. 

