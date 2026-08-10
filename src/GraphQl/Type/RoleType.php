<?php

declare(strict_types=1);

namespace App\GraphQl\Type;

use App\Model\User\Role;
use GraphQL\Error\Error;
use GraphQL\Language\AST\EnumValueNode;
use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Utils\Utils;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;

final class RoleType extends EnumType implements AliasedInterface
{
    private const string NAME = 'Role';

    public function __construct()
    {
        $config = [
            'name'        => self::NAME,
            'values'      => Role::namesToValues(),
            'description' => 'Available User roles.',
        ];

        parent::__construct($config);
    }

    #[\Override]
    public function serialize(mixed $value): string
    {
        if (\is_string($value)) {
            $value = Role::tryFrom($value) ?? $value;
        }

        if ($value instanceof Role) {
            return $value->value;
        }

        throw new Error('Cannot serialize Role value as enum: '.Utils::printSafe($value));
    }

    #[\Override]
    public function parseValue(mixed $value): Role
    {
        return Role::tryFrom($value)
            ?? throw new Error('Cannot represent value as Role enum: '.Utils::printSafe($value));
    }

    #[\Override]
    public function parseLiteral(Node $valueNode, ?array $variables = null): ?Role
    {
        if (!$valueNode instanceof EnumValueNode) {
            return null;
        }

        return $this->parseValue($valueNode->value);
    }

    public static function getAliases(): array
    {
        return [self::NAME];
    }
}
