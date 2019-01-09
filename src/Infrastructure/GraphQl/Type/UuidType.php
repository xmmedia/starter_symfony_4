<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Type;

use App\Model\UuidInterface;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Ramsey\Uuid\Uuid;

final class UuidType extends ScalarType implements AliasedInterface
{
    private const NAME = 'UUID';

    public function __construct()
    {
        parent::__construct([
            'name'        => self::NAME,
            'description' => 'A UUID represented as string.',
        ]);
    }

    public function serialize($value): ?string
    {
        if ($value instanceof UuidInterface) {
            return $value->toString();
        }

        return \is_string($value) && Uuid::isValid($value) ? $value : null;
    }

    public function parseValue($value): ?string
    {
        return \is_string($value) && Uuid::isValid($value) ? $value : null;
    }

    public function parseLiteral($valueNode, array $variables = null): ?string
    {
        if (!$valueNode instanceof StringValueNode) {
            return null;
        }

        return $this->parseValue($valueNode->value);
    }

    public static function getAliases(): array
    {
        return [self::NAME];
    }
}
