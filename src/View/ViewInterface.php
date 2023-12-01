<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\View;

use Spyck\VisualizationBundle\Model\Dashboard;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(tags: ['spyck.visualization.view'])]
interface ViewInterface
{
    public const CSV = 'csv';
    public const CSV_NAME = 'CSV';
    public const HTML = 'html';
    public const HTML_NAME = 'HTML';
    public const JSON = 'json';
    public const JSON_NAME = 'JSON';
    public const PDF = 'pdf';
    public const PDF_NAME = 'PDF';
    public const SSV = 'ssv';
    public const SSV_NAME = 'CSV (Semicolon)';
    public const TABLE = 'table';
    public const TABLE_NAME = 'Table';
    public const TSV = 'tsv';
    public const TSV_NAME = 'TSV';
    public const XLSX = 'xlsx';
    public const XLSX_NAME = 'XLSX';
    public const XML = 'xml';
    public const XML_NAME = 'XML';

    public function getContent(Dashboard $dashboard): string;

    public function getFile(string $name, array $parameters): string;

    public static function getContentType(): string;

    public static function getExtension(): string;

    public static function getName(): string;

    /**
     * Return boolean if it can be merged or not, or NULL when it doesn't matter.
     */
    public static function isMerge(): ?bool;
}
