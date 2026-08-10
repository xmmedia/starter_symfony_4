<?php

declare(strict_types=1);

namespace App\GraphQl\Type;

use App\Model\AuthLog\AuthLogEventType;
use GraphQL\Error\Error;
use GraphQL\Language\AST\EnumValueNode;
use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Utils\Utils;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;

final class AuthLogEventTypeType extends EnumType implements AliasedInterface
{
    private const string NAME = 'AuthLogEventType';

    public function __construct()
    {
        $config = [
            'name'        => self::NAME,
            'values'      => AuthLogEventType::namesToValues(),
            'description' => 'Authentication event event types.',
        ];

        parent::__construct($config);
    }

    #[\Override]
    public function serialize(mixed $value): string
    {
        if (\is_string($value)) {
            $value = AuthLogEventType::tryFrom($value) ?? $value;
        }

        if ($value instanceof AuthLogEventType) {
            return $value->value;
        }

        throw new Error('Cannot serialize AuthLogEventType value as enum: '.Utils::printSafe($value));
    }

    #[\Override]
    public function parseValue(mixed $value): AuthLogEventType
    {
        return AuthLogEventType::tryFrom($value)
            ?? throw new Error('Cannot represent value as AuthLogEventType enum: '.Utils::printSafe($value));
    }

    #[\Override]
    public function parseLiteral(Node $valueNode, ?array $variables = null): ?AuthLogEventType
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
