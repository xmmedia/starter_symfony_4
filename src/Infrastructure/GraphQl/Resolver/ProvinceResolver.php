<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Resolver;

use App\DataProvider\ProvinceProvider;
use App\Model\Province;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class ProvinceResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @return Province[]
     */
    public function all(): array
    {
        return array_values(array_map(function (string $province) {
            return Province::fromString($province);
        }, ProvinceProvider::all(false)));
    }

    public static function getAliases(): array
    {
        return [
            'all' => 'app.graphql.resolver.province.all',
        ];
    }
}
