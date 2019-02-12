<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Resolver;

use App\DataProvider\CountryProvider;
use App\Model\Country;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class CountryResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @return Country[]
     */
    public function all(): array
    {
        return array_values(array_map(function (string $country) {
            return Country::fromString($country);
        }, CountryProvider::all()));
    }

    public static function getAliases(): array
    {
        return [
            'all' => 'app.graphql.resolver.country.all',
        ];
    }
}
