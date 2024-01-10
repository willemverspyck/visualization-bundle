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

        $builder->setParameter('spyck.visualization.config.cache.active', $config['cache']['active']);
        $builder->setAlias('spyck.visualization.config.cache.adapter', $config['cache']['adapter']);

        $builder->setParameter('spyck.visualization.config.chart.command', $config['chart']['command']);
        $builder->setParameter('spyck.visualization.config.chart.directory', $config['chart']['directory']);

        $builder->setParameter('spyck.visualization.config.directory', $config['directory']);

        $builder->setParameter('spyck.visualization.config.mail.fromEmail', $config['mail']['fromEmail']);
        $builder->setParameter('spyck.visualization.config.mail.fromName', $config['mail']['fromName']);

        $builder->setParameter('spyck.visualization.config.request', $config['request']);

        $builder->setParameter('spyck.visualization.config.user.class', $config['user']['class']);
    }
}