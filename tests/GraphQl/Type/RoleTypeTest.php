<?php

declare(strict_types=1);

namespace App\Tests\GraphQl\Type;

use App\GraphQl\Type\RoleType;
use App\Model\User\Role;
use App\Tests\BaseTestCase;
use GraphQL\Error\Error;
use GraphQL\Language\AST\EnumValueNode;
use GraphQL\Language\AST\StringValueNode;
use PHPUnit\Framework\Attributes\DataProvider;

class RoleTypeTest extends BaseTestCase
{
    #[DataProvider('roleProvider')]
    public function testSerialize(Role|string $value, string $expected): void
    {
        $result = new RoleType()->serialize($value);

        $this->assertEquals($expected, $result);
    }

    public function testSerializeNotRole(): void
    {
        $this->expectException(Error::class);

        new RoleType()->serialize(1);
    }

    #[DataProvider('roleParseProvider')]
    public function testParseValue(Role $expected, string $value): void
    {
        $result = new RoleType()->parseValue($value);

        $this->assertSame($expected, $result);
    }

    public static function roleParseProvider(): \Generator
    {
        yield [
            Role::ROLE_USER,
            'ROLE_USER',
        ];
    }

    public static function roleProvider(): \Generator
    {
        yield [
            Role::ROLE_USER,
            'ROLE_USER',
        ];

        yield [
            'ROLE_USER',
            'ROLE_USER',
        ];
    }

    public function testParseLiteral(): void
    {
        $valueNode = new EnumValueNode([]);
        $valueNode->value = 'ROLE_USER';

        $result = new RoleType()->parseLiteral($valueNode);

        $this->assertSame(Role::ROLE_USER, $result);
    }

    public function testParseLiteralNotEnum(): void
    {
        $valueNode = new StringValueNode(['value' => 'ROLE_USER']);

        $result = new RoleType()->parseLiteral($valueNode);

        $this->assertNull($result);
    }

    public function testParseValueInvalid(): void
    {
        $this->expectException(\Exception::class);

        new RoleType()->parseValue('asdf');
    }

    public function testAliases(): void
    {
        $result = RoleType::getAliases();

        $this->assertEquals(['Role'], $result);
    }
}
