<?php

declare(strict_types=1);

namespace App\GraphQl\Type;

use App\Model\User\UserId;
use GraphQL\Error\Error;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Utils\Utils;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Ramsey\Uuid\Uuid;
use Xm\SymfonyBundle\Infrastructure\GraphQl\Type\UuidTypeTrait;

class UserIdType extends ScalarType implements AliasedInterface
{
    use UuidTypeTrait;

    private const string NAME = 'UserId';

    public function __construct()
    {
        parent::__construct([
            'name'        => self::NAME,
            'description' => 'A UUID for a User represented as string.',
        ]);
    }

    /**
     * @param string|mixed $value
     */
    public function parseValue($value): ?UserId
    {
        if (\is_string($value) && Uuid::isValid($value)) {
            return UserId::fromString($value);
        }

        throw new Error('Cannot represent value as UUID: '.Utils::printSafe($value));
    }

    public static function getAliases(): array
    {
        return [self::NAME];
    }
}
