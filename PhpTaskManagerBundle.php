<?php

namespace SunValley\TaskManager\Symfony;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PhpTaskManagerBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    }

}