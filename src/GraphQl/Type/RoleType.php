<?php

declare(strict_types=1);

namespace App\GraphQl\Type;

use App\Model\User\Role;
use GraphQL\Error\Error;
use GraphQL\Language\AST\EnumValueNode;
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
            'values'      => array_combine(
                Role::getNames(),
                Role::getValues(),
            ),
            'description' => 'Available User roles.',
        ];

        parent::__construct($config);
    }

    /**
     * @param Role|string $value
     */
    #[\Override]
    public function serialize($value): ?string
    {
        if (null === $value) {
            return null;
        }

        if (\is_string($value)) {
            $value = Role::byValue($value);
        }

        if ($value instanceof Role) {
            return $value->getValue();
        }

        throw new Error('Cannot serialize Role value as enum: '.Utils::printSafe($value));
    }

    /**
     * @param string $value
     */
    #[\Override]
    public function parseValue($value): ?Role
    {
        if (null === $value) {
            return null;
        }

        return Role::byValue($value);
    }

    /**
     * @param EnumValueNode $valueNode
     */
    #[\Override]
    public function parseLiteral($valueNode, ?array $variables = null): ?Role
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
