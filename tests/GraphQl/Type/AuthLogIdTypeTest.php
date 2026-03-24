<?php

declare(strict_types=1);

namespace App\Tests\GraphQl\Type;

use App\GraphQl\Type\AuthLogIdType;
use App\Model\AuthLog\AuthLogId;
use App\Tests\BaseTestCase;
use GraphQL\Error\Error;

class AuthLogIdTypeTest extends BaseTestCase
{
    public function testParseValue(): void
    {
        $uuid = $this->faker()->uuid();

        $result = new AuthLogIdType()->parseValue($uuid);

        $this->assertInstanceOf(AuthLogId::class, $result);
        $this->assertSame($uuid, $result->toString());
    }

    public function testParseValueInvalidUuid(): void
    {
        $this->expectException(Error::class);

        new AuthLogIdType()->parseValue('not-a-uuid');
    }

    public function testParseValueNonString(): void
    {
        $this->expectException(Error::class);

        new AuthLogIdType()->parseValue(123);
    }

    public function testAliases(): void
    {
        $result = AuthLogIdType::getAliases();

        $this->assertEquals(['AuthLogId'], $result);
    }
}
