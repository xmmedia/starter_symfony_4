<?php

declare(strict_types=1);

namespace App\GraphQl\Type;

use App\Model\AuthLog\AuthLogEventType;
use GraphQL\Type\Definition\EnumType;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;

final class AuthLogEventTypeType extends EnumType implements AliasedInterface
{
    private const string NAME = 'AuthLogEventType';

    public function __construct()
    {
        $config = [
            'name'        => self::NAME,
            'values'      => array_combine(
                AuthLogEventType::getNames(),
                AuthLogEventType::getValues(),
            ),
            'description' => 'Authentication event event types.',
        ];

        parent::__construct($config);
    }

    public static function getAliases(): array
    {
        return [self::NAME];
    }
}
