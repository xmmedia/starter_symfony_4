<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\GraphQl\Type;

use App\Infrastructure\GraphQl\Type\UuidType;
use App\Tests\BaseTestCase;
use GraphQL\Language\AST\StringValueNode;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class UuidTypeTest extends BaseTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @dataProvider uuidProvider
     */
    public function testSerialize($value, ?string $expected): void
    {
        $result = (new UuidType())->serialize($value);

        $this->assertEquals($expected, $result);
    }

    public function testSerializerUserId(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $result = (new UuidType())->serialize($userId);

        $this->assertEquals($userId->toString(), $result);
    }

    /**
     * @dataProvider uuidProvider
     */
    public function testParseValue($value, ?string $expected): void
    {
        $result = (new UuidType())->parseValue($value);

        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider uuidProvider
     */
    public function testParseLiteral($value, ?string $expected): void
    {
        $node = new StringValueNode([]);
        $node->value = $value;

        $result = (new UuidType())->parseLiteral($node);

        $this->assertEquals($expected, $result);
    }

    public function uuidProvider(): \Generator
    {
        $faker = $this->faker();

        $userId = $faker->userId;

        yield [
            $userId->toString(),
            $userId->toString(),
        ];

        yield [
            'string',
            null,
        ];

        yield [
            null,
            null,
        ];

        yield [
            [],
            null,
        ];
    }

    public function testParseLiteralNotStringValueNode(): void
    {
        $result = (new UuidType())->parseLiteral('string');

        $this->assertNull($result);
    }

    public function testAliases(): void
    {
        $result = UuidType::getAliases();

        $this->assertEquals(['UUID'], $result);
    }
}
