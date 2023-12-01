<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class SpyckVisualizationBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');

        $builder->setParameter('spyck.visualization.cache', $config['cache']);

        $builder->setParameter('spyck.visualization.chart.command', $config['chart']['command']);
        $builder->setParameter('spyck.visualization.chart.directory', $config['chart']['directory']);

        $builder->setParameter('spyck.visualization.directory', $config['directory']);

        $builder->setParameter('spyck.visualization.mailer.from_email', $config['mailer']['fromEmail']);
        $builder->setParameter('spyck.visualization.mailer.from_name', $config['mailer']['fromName']);

        $builder->setParameter('spyck.visualization.request', $config['request']);

        $builder->setParameter('spyck.visualization.user.class', $config['user']['class']);
    }
}