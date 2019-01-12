<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Type;

use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;

final class DateTimeType extends ScalarType implements AliasedInterface
{
    private const NAME = 'DateTime';

    public function __construct()
    {
        parent::__construct([
            'name'        => self::NAME,
            'description' => 'A date & time represented as string.',
        ]);
    }

    public function serialize($value): ?string
    {
        return $value->format(\DateTime::RFC3339);
    }

    public function parseValue($value): ?\DateTimeImmutable
    {
        if (null === $value) {
            return null;
        }

        return new \DateTimeImmutable($value);
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
