<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Resolver;

use App\DataProvider\ProvinceProvider;
use App\Model\Province;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class ProvinceResolver implements ResolverInterface
{
    /**
     * @return Province[]
     */
    public function __invoke(): array
    {
        return array_values(array_map(function (string $province) {
            return Province::fromString($province);
        }, ProvinceProvider::all(false)));
    }
}
