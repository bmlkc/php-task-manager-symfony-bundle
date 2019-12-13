<?php

namespace SunValley\TaskManager\Symfony\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use SunValley\TaskManager\Client;
use SunValley\TaskManager\ProgressReporter;
use SunValley\TaskManager\Symfony\Tests\Fixtures\SampleContainerAwareTask;
use SunValley\TaskManager\TaskStorage\RedisTaskStorage;
use Symfony\Component\Yaml\Parser;

class PhpTaskManagerExtensionTest extends TestCase
{

    public function testBasicConfig()
    {
        $container = $this->buildContainer($this->getEmptyConfig());
        $client    = $container->get('php_task_manager_client');
        $this->assertInstanceOf(Client::class, $client);

        $configuration = $container->get('SunValley\TaskManager\Configuration');
        $this->assertEquals(1, $configuration->getMinProcesses());
        $this->assertEquals(10, $configuration->getMaxProcesses());
        $this->assertEquals(60, $configuration->getTtl());
        $this->assertEquals(10, $configuration->getMaxJobsPerProcess());
    }

    public function testFullConfig()
    {
        $container = $this->buildContainer($this->getFullConfig());
        $storage   = $container->get('php_task_manager_storage');
        $this->assertInstanceOf(RedisTaskStorage::class, $storage);

        $configuration = $container->get('SunValley\TaskManager\Configuration');
        $this->assertEquals(2, $configuration->getMinProcesses());
        $this->assertEquals(12, $configuration->getMaxProcesses());
        $this->assertEquals(75, $configuration->getTtl());
        $this->assertEquals(5, $configuration->getMaxJobsPerProcess());
    }

    /** @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException */
    public function testInvalidConfigWithoutQueue()
    {
        $config = $this->getFullConfig();
        unset($config['task_queue']);
        $this->buildContainer($config);
    }

    /** @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException */
    public function testInvalidConfigWrongMinProcess()
    {
        $config                              = $this->getFullConfig();
        $config['pool']['minimum_processes'] = 0;
        $this->buildContainer($config);
    }

    /** @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException */
    public function testInvalidConfigWrongMaxProcess()
    {
        $config                              = $this->getFullConfig();
        $config['pool']['maximum_processes'] = 0;
        $this->buildContainer($config);
    }

    /** @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException */
    public function testInvalidConfigTimeToLive()
    {
        $config                         = $this->getFullConfig();
        $config['pool']['time_to_live'] = '-1';
        $this->buildContainer($config);
    }

    /** @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException */
    public function testInvalidConfigMaxJobsPerProcess()
    {
        $config                                 = $this->getFullConfig();
        $config['pool']['max_jobs_per_process'] = '-1';
        $this->buildContainer($config);
    }

    /** @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException */
    public function testInvalidConfigWrongMinAndMaxProcess()
    {
        $config                              = $this->getFullConfig();
        $config['pool']['maximum_processes'] = '1';
        $config['pool']['minimum_processes'] = '12';
        $this->buildContainer($config);
    }

    public function testTaskEval()
    {
        $config    = $this->getFullConfig();
        $container = $this->buildContainer($config);
        $factory   = $container->get('php_task_manager_factory');
        $loop      = $factory->getLoop();
        $manager   = $factory->generate();
        $uniqid    = uniqid();
        putenv('PTM_TEST_KERNEL_CONFIG=' . base64_encode(serialize(['php_task_manager' => $config])));
        $promise = $manager->submitTask(new SampleContainerAwareTask($uniqid, ['data' => '543']));
        $result  = null;
        $error   = null;
        $promise->then(
            function (ProgressReporter $reporter) use ($loop, &$result, &$error) {
                $result = $reporter->getResult();
                $error  = $reporter->getError();
                $loop->stop();
            },
            function ($err) use ($loop, &$error) {
                $error = $err;
                $loop->stop();
            }
        );
        //timeout
        $loop->addTimer(
            5,
            function () use ($loop) {
                $loop->stop();
            }
        );
        $loop->run();

        $this->assertEmpty($error, $error);
        $this->assertEquals('Result: 543', $result);
        putenv('PTM_TEST_KERNEL_CONFIG=');
    }

    protected function buildContainer(array $config): ContainerInterface
    {
        $kernel = new TestKernel('dev', true);
        $kernel->setExtensionConfigs(
            [
                'php_task_manager' => $config,
            ]
        );
        $kernel->boot();

        return $kernel->getContainer();
    }

    protected function getEmptyConfig()
    {
        $yaml = <<<EOF
task_queue: redis://127.0.0.1:6380
EOF;

        $parser = new Parser();

        return $parser->parse($yaml);
    }

    protected function getFullConfig()
    {
        $yaml = <<<EOF
task_queue: redis://127.0.0.1:6380
task_storage: redis://127.0.0.1:6380
pool:
    minimum_processes: 2
    maximum_processes: 12
    time_to_live: 75
    max_jobs_per_process: 5
EOF;

        $parser = new Parser();

        return $parser->parse($yaml);
    }
}