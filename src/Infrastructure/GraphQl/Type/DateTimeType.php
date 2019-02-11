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
            'description' => 'Date & time represented as string.',
        ]);
    }

    /**
     * @param \DateTimeInterface $value
     */
    public function serialize($value): ?string
    {
        if (null === $value) {
            return null;
        }

        return $value->format(\DateTimeInterface::RFC3339);
    }

    /**
     * @param string $value
     */
    public function parseValue($value): ?\DateTimeImmutable
    {
        if (null === $value) {
            return null;
        }

        return new \DateTimeImmutable($value);
    }

    /**
     * @param StringValueNode $valueNode
     */
    public function parseLiteral($valueNode, array $variables = null): ?\DateTimeImmutable
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
