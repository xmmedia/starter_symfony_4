<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Type;

use App\Model\Gender;
use GraphQL\Error\Error;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Utils\Utils;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;

class GenderType extends EnumType implements AliasedInterface
{
    private const NAME = 'Gender';

    public function __construct()
    {
        $config = [
            'name'        => self::NAME,
            'values'      => array_combine(
                Gender::getNames(),
                Gender::getValues()
            ),
            'description' => 'Gender.',
        ];

        parent::__construct($config);
    }

    /**
     * @param Gender $value
     */
    public function serialize($value)
    {
        if ($value instanceof Gender) {
            return $value->getValue();
        }

        throw new Error(
            'Cannot serialize Gender value as enum: '.Utils::printSafe($value)
        );
    }

    public static function getAliases(): array
    {
        return [self::NAME];
    }
}
