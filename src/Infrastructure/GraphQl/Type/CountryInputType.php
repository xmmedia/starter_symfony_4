<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Type;

use App\DataProvider\CountryProvider;
use App\Model\Country;
use GraphQL\Error\Error;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Utils\Utils;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;

class CountryInputType extends EnumType implements AliasedInterface
{
    private const NAME = 'CountryInput';

    public function __construct()
    {
        $config = [
            'values' => CountryProvider::abbreviations(),
        ];

        parent::__construct($config);
    }

    /**
     * @param Country $value
     */
    public function serialize($value)
    {
        if ($value instanceof Country) {
            return $value->toString();
        }

        throw new Error(
            'Cannot serialize Country value as enum: '.Utils::printSafe($value)
        );
    }

    public static function getAliases(): array
    {
        return [self::NAME];
    }
}
