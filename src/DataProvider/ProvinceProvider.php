<?php

declare(strict_types=1);

namespace App\DataProvider;

use App\Exception\InvalidProvince;
use App\Model\Country;

class ProvinceProvider
{
    /**
     * @var array
     */
    private static $provincesByCountry = [
        'CA' => [
            'Alberta'                 => 'AB',
            'British Columbia'        => 'BC',
            'Manitoba'                => 'MB',
            'New Burnswick'           => 'NB',
            'Newfoundland & Labrador' => 'NL',
            'Nova Scotia'             => 'NS',
            'Northwest Territories'   => 'NT',
            'Nunavut'                 => 'NU',
            'Ontario'                 => 'ON',
            'Prince Edward Island'    => 'PE',
            'Quebec'                  => 'QC',
            'Saskatchewan'            => 'SK',
            'Yukon'                   => 'YT',
        ],
        'US' => [
            'Alabama'              => 'AL',
            'Alaska'               => 'AK',
            'Arizona'              => 'AZ',
            'Arkansas'             => 'AR',
            'California'           => 'CA',
            'Colorado'             => 'CO',
            'Connecticut'          => 'CT',
            'Delaware'             => 'DE',
            'District of Columbia' => 'DC',
            'Florida'              => 'FL',
            'Georgia'              => 'GA',
            'Hawaii'               => 'HI',
            'Idaho'                => 'ID',
            'Illinois'             => 'IL',
            'Indiana'              => 'IN',
            'Iowa'                 => 'IA',
            'Kansas'               => 'KS',
            'Kentucky'             => 'KY',
            'Louisiana'            => 'LA',
            'Maine'                => 'ME',
            'Maryland'             => 'MD',
            'Massachusetts'        => 'MA',
            'Michigan'             => 'MI',
            'Minnesota'            => 'MN',
            'Mississippi'          => 'MS',
            'Missouri'             => 'MO',
            'Montana'              => 'MT',
            'Nebraska'             => 'NE',
            'Nevada'               => 'NV',
            'New Hampshire'        => 'NH',
            'New Jersey'           => 'NJ',
            'New Mexico'           => 'NM',
            'New York'             => 'NY',
            'North Carolina'       => 'NC',
            'North Dakota'         => 'ND',
            'Ohio'                 => 'OH',
            'Oklahoma'             => 'OK',
            'Oregon'               => 'OR',
            'Pennsylvania'         => 'PA',
            'Rhode Island'         => 'RI',
            'South Carolina'       => 'SC',
            'South Dakota'         => 'SD',
            'Tennessee'            => 'TN',
            'Texas'                => 'TX',
            'Utah'                 => 'UT',
            'Vermont'              => 'VT',
            'Virginia'             => 'VA',
            'Washington'           => 'WA',
            'West Virginia'        => 'WV',
            'Wisconsin'            => 'WI',
            'Wyoming'              => 'WY',
        ],
    ];

    /**
     * All provinces where the key is the name and the value the abbreviation,
     * optionally grouped by country.
     * If not grouped by country, Canadian provinces will be first
     * and then US states.
     */
    public static function all(bool $byCountry = true): array
    {
        if ($byCountry) {
            return self::$provincesByCountry;
        }

        return array_merge(self::$provincesByCountry['CA'], self::$provincesByCountry['US']);
    }

    /**
     * Just the ISO provincial abbreviations, optionally grouped by country.
     */
    public static function abbreviations(bool $byCountry = true): array
    {
        $abbreviations = array_map(function (array $provinces): array {
            return array_values($provinces);
        }, self::$provincesByCountry);

        if ($byCountry) {
            return $abbreviations;
        }

        return call_user_func_array('array_merge', $abbreviations);
    }

    public static function name(string $province): string
    {
        return array_search($province, ProvinceProvider::all(false));
    }

    public static function country(string $province): Country
    {
        foreach (self::$provincesByCountry as $country => $provinces) {
            if (in_array($province, $provinces)) {
                return Country::fromString($country);
            }
        }

        throw InvalidProvince::invalid($province);
    }
}
