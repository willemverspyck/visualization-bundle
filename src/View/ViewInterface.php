<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\View;

use Spyck\VisualizationBundle\Model\Dashboard;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(tags: ['spyck.visualization.view'])]
interface ViewInterface
{
    public const string CSV = 'csv';
    public const string JSON = 'json';
    public const string PDF = 'pdf';
    public const string SSV = 'ssv';
    public const string TSV = 'tsv';
    public const string XLSX = 'xlsx';
    public const string XML = 'xml';

    public function getContent(Dashboard $dashboard): string;

    public function getFile(string $name, array $parameters = []): string;

    public static function getContentType(): string;

    public static function getExtension(): string;

    public static function getName(): string;

    /**
     * Return boolean if it can be merged or not, or NULL when it doesn't matter.
     */
    public static function isMerge(): ?bool;
}
