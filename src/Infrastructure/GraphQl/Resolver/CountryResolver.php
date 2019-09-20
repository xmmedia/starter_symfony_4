<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Resolver;

use App\DataProvider\CountryProvider;
use App\Model\Country;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class CountryResolver implements ResolverInterface
{
    /**
     * @return Country[]
     */
    public function __invoke(): array
    {
        return array_values(array_map(function (string $country) {
            return Country::fromString($country);
        }, CountryProvider::all()));
    }
}
