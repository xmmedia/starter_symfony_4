<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\GraphQl\Type;

use App\Infrastructure\GraphQl\Type\RoleType;
use App\Model\User\Role;
use GraphQL\Error\Error;
use GraphQL\Language\AST\EnumValueNode;
use GraphQL\Language\AST\FieldNode;
use PHPUnit\Framework\TestCase;

class RoleTypeTest extends TestCase
{
    /**
     * @dataProvider dateProvider
     */
    public function testSerialize(Role|string|null $value, ?string $expected): void
    {
        $result = (new RoleType())->serialize($value);

        $this->assertEquals($expected, $result);
    }

    public function testSerializeNotRole(): void
    {
        $this->expectException(Error::class);

        (new RoleType())->serialize(1);
    }

    /**
     * @dataProvider dateProvider
     */
    public function testParseValue(Role|string|null $expected, ?string $value): void
    {
        $result = (new RoleType())->parseValue($value);

        $this->assertEquals($expected, $result);
    }

    public function dateProvider(): \Generator
    {
        yield [
            Role::ROLE_USER(),
            'ROLE_USER',
        ];

        yield [
            'ROLE_USER',
            'ROLE_USER',
        ];

        yield [
            null,
            null,
        ];
    }

    /**
     * @dataProvider dateProviderParseLiteral
     */
    public function testParseLiteral(Role|string $expected, ?string $value): void
    {
        $valueNode = new EnumValueNode([]);
        $valueNode->value = $value;

        $result = (new RoleType())->parseLiteral($valueNode);

        $this->assertEquals($expected, $result);
    }

    public function dateProviderParseLiteral(): \Generator
    {
        yield [
            Role::ROLE_USER(),
            'ROLE_USER',
        ];

        yield [
            'ROLE_USER',
            'ROLE_USER',
        ];
    }

    public function testParseLiteralNotEnum(): void
    {
        $valueNode = new FieldNode([]);

        $result = (new RoleType())->parseLiteral($valueNode);

        $this->assertNull($result);
    }

    public function testParseValueInvalid(): void
    {
        $this->expectException(\Exception::class);

        (new RoleType())->parseValue('asdf');
    }

    public function testAliases(): void
    {
        $result = RoleType::getAliases();

        $this->assertEquals(['Role'], $result);
    }
}
