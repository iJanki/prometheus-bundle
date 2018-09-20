<?php

namespace Ijanki\Bundle\PrometheusBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Ijanki\Bundle\PrometheusBundle\DependencyInjection\CollectorCompilerPass;
use Ijanki\Bundle\PrometheusBundle\DependencyInjection\StorageAdapterCompilerPass;

class IjankiPrometheusBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new CollectorCompilerPass());
        $container->addCompilerPass(new StorageAdapterCompilerPass());
    }

}
