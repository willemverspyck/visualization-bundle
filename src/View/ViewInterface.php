<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\View;

use Spyck\VisualizationBundle\Model\Dashboard;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(tags: ['spyck.visualization.view'])]
interface ViewInterface
{
    public const CSV = 'csv';
    public const HTML = 'html';
    public const JSON = 'json';
    public const PDF = 'pdf';
    public const SSV = 'ssv';
    public const TABLE = 'table';
    public const TSV = 'tsv';
    public const XLSX = 'xlsx';
    public const XML = 'xml';

    public function getContent(Dashboard $dashboard): string;

    public function getFile(string $name, array $parameters): string;

    public static function getContentType(): string;

    public static function getExtension(): string;

    public static function getName(): string;

    public static function getDescription(): string;

    /**
     * Return boolean if it can be merged or not, or NULL when it doesn't matter.
     */
    public static function isMerge(): ?bool;
}
