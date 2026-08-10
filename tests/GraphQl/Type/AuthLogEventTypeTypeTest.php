<?php

declare(strict_types=1);

namespace App\Tests\GraphQl\Type;

use App\GraphQl\Type\AuthLogEventTypeType;
use App\Model\AuthLog\AuthLogEventType;
use App\Tests\BaseTestCase;
use GraphQL\Error\Error;
use GraphQL\Language\AST\EnumValueNode;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\EnumValueDefinition;
use PHPUnit\Framework\Attributes\DataProvider;

class AuthLogEventTypeTypeTest extends BaseTestCase
{
    #[DataProvider('eventTypeProvider')]
    public function testSerialize(AuthLogEventType|string $value, string $expected): void
    {
        $result = new AuthLogEventTypeType()->serialize($value);

        $this->assertEquals($expected, $result);
    }

    public function testSerializeNotEventType(): void
    {
        $this->expectException(Error::class);

        new AuthLogEventTypeType()->serialize(1);
    }

    public function testParseValue(): void
    {
        $result = new AuthLogEventTypeType()->parseValue('LOGIN');

        $this->assertSame(AuthLogEventType::LOGIN, $result);
    }

    public function testParseValueInvalid(): void
    {
        $this->expectException(Error::class);

        new AuthLogEventTypeType()->parseValue('asdf');
    }

    public function testParseLiteral(): void
    {
        $valueNode = new EnumValueNode([]);
        $valueNode->value = 'LOGIN';

        $result = new AuthLogEventTypeType()->parseLiteral($valueNode);

        $this->assertSame(AuthLogEventType::LOGIN, $result);
    }

    public function testParseLiteralNotEnum(): void
    {
        $valueNode = new StringValueNode(['value' => 'LOGIN']);

        $result = new AuthLogEventTypeType()->parseLiteral($valueNode);

        $this->assertNull($result);
    }

    public static function eventTypeProvider(): \Generator
    {
        yield [
            AuthLogEventType::LOGIN,
            'LOGIN',
        ];

        yield [
            'LOGIN',
            'LOGIN',
        ];
    }

    public function testAliases(): void
    {
        $result = AuthLogEventTypeType::getAliases();

        $this->assertEquals(['AuthLogEventType'], $result);
    }

    public function testValues(): void
    {
        $type = new AuthLogEventTypeType();
        $values = $type->getValues();
        $valueNames = array_map(static fn (EnumValueDefinition $v): string => $v->name, $values);

        $this->assertContains('LOGIN', $valueNames);
        $this->assertContains('LOGIN_FAILED', $valueNames);
        $this->assertContains('IMPERSONATION_STARTED', $valueNames);
        $this->assertContains('IMPERSONATION_ENDED', $valueNames);
    }

    public function testValuesMappedFromEnum(): void
    {
        $type = new AuthLogEventTypeType();
        $values = $type->getValues();
        $valueMap = [];

        foreach ($values as $v) {
            $valueMap[$v->name] = $v->value;
        }

        $this->assertSame(AuthLogEventType::LOGIN->value, $valueMap['LOGIN']);
        $this->assertSame(AuthLogEventType::LOGIN_FAILED->value, $valueMap['LOGIN_FAILED']);
        $this->assertSame(AuthLogEventType::IMPERSONATION_STARTED->value, $valueMap['IMPERSONATION_STARTED']);
        $this->assertSame(AuthLogEventType::IMPERSONATION_ENDED->value, $valueMap['IMPERSONATION_ENDED']);
    }
}
