<?php

declare(strict_types=1);

namespace App\DataProvider;

class CountryProvider
{
    /**
     * @var array
     */
    private static $countries = [
        'Canada'        => 'CA',
        'United States' => 'US',
    ];

    /**
     * All countries where the key is the name and the value the abbreviation.
     */
    public static function all(): array
    {
        return self::$countries;
    }

    /**
     * Just the ISO country abbreviations.
     */
    public static function abbreviations(): array
    {
        return array_values(self::$countries);
    }

    public static function name(string $country): string
    {
        return array_search($country, self::$countries);
    }
}
