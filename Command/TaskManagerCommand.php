<?php

namespace SunValley\TaskManager\Symfony\Command;

use SunValley\TaskManager\Symfony\Task\TaskManagerFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TaskManagerCommand extends Command
{

    /** @var TaskManagerFactory */
    protected $managerFactory;

    protected static $defaultName = 'task:manager';

    public function __construct(TaskManagerFactory $managerFactory)
    {
        parent::__construct();

        $this->managerFactory = $managerFactory;
    }

    protected function configure()
    {
        $this->setName(static::$defaultName)
             ->setDescription('Starts the task manager');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = $this->managerFactory->generate();
        $this->managerFactory->getLoop()->run();
        return 0;
    }
}