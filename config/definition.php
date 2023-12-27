<?php

declare(strict_types=1);

use App\Utility\DateTimeUtility;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->arrayNode('cache')
                ->children()
                    ->booleanNode('active')
                        ->defaultTrue()
                    ->end()
                    ->scalarNode('adapter')
                        ->defaultValue('cache.app')
                        ->cannotBeEmpty()
                    ->end()
                ->end()
                ->addDefaultsIfNotSet()
            ->end()
        ->end()
        ->children()
            ->arrayNode('chart')
                ->children()
                    ->scalarNode('command')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('directory')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                ->end()
                ->isRequired()
            ->end()
        ->end()
        ->children()
            ->scalarNode('directory')
                ->isRequired()
                ->cannotBeEmpty()
            ->end()
        ->end()
        ->children()
            ->arrayNode('mailer')
                ->children()
                    ->scalarNode('fromEmail')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('fromName')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                ->end()
                ->isRequired()
            ->end()
        ->end()
        ->children()
            ->arrayNode('request')
                ->children()
                    ->scalarNode('dayParameter')
                        ->defaultValue(DateTimeUtility::getDate('today', DateTimeUtility::FORMAT_DATE))
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('dayStartParameter')
                        ->defaultValue(DateTimeUtility::getDate('-8 days', DateTimeUtility::FORMAT_DATE))
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('dayEndParameter')
                        ->defaultValue(DateTimeUtility::getDate('-1 day', DateTimeUtility::FORMAT_DATE))
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('monthStartParameter')
                        ->defaultValue(DateTimeUtility::getDate('First Day of 12 Months Ago', DateTimeUtility::FORMAT_DATE))
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('monthEndParameter')
                        ->defaultValue(DateTimeUtility::getDate('Last Day of Last Month', DateTimeUtility::FORMAT_DATE))
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('weekStartParameter')
                        ->defaultValue(DateTimeUtility::getDate('First Monday of 52 Weeks Ago', DateTimeUtility::FORMAT_DATE))
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('weekEndParameter')
                        ->defaultValue(DateTimeUtility::getDate('First Sunday of Last Week', DateTimeUtility::FORMAT_DATE))
                        ->cannotBeEmpty()
                    ->end()
                    ->integerNode('limitFilter')
                        ->defaultValue(25)
                        ->min(1)
                    ->end()
                    ->integerNode('offsetFilter')
                        ->defaultValue(0)
                        ->min(0)
                    ->end()
                ->end()
                ->addDefaultsIfNotSet()
            ->end()
        ->end()
        ->children()
            ->arrayNode('user')
                ->children()
                    ->scalarNode('class')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                ->end()
                ->isRequired()
            ->end()
        ->end();
};