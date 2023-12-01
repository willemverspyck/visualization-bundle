<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Utility;

use Exception;

final class NumberUtility
{
    /**
     * Convert 50.000 to 50K, 1.000.000 to 1M.
     *
     * @throws Exception
     */
    public static function getAbbreviation(float|int $data, int $precision = 0): string
    {
        $abbreviations = [6 => 'M', 3 => 'K', 0 => ''];

        foreach ($abbreviations as $exponent => $abbreviation) {
            if (abs($data) >= pow(10, $exponent)) {
                $number = $data / pow(10, $exponent);

                if (0.0 === round($number - floor($number), 1)) {
                    $precision = 0;
                }

                return sprintf('%s%s', round($number, $precision), $abbreviation);
            }
        }

        return sprintf('%s', round($data, $precision));
    }
}
