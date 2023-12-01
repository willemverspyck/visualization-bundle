<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Utility;

use Symfony\Component\String\Slugger\AsciiSlugger;

final class FileUtility
{
    /**
     * Remove strange characters from the filename.
     */
    public static function filter(string $name): string
    {
        $slugger = new AsciiSlugger();

        return $slugger->slug($name)->lower()->toString();
    }
}
