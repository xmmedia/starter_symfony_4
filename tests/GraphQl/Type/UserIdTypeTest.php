<?php

declare(strict_types=1);

namespace App\Tests\GraphQl\Type;

use App\GraphQl\Type\UserIdType;
use App\Tests\BaseTestCase;

class UserIdTypeTest extends BaseTestCase
{
    public function testParseValue(): void
    {
        $userId = $this->faker()->userId()->toString();

        $result = new UserIdType()->parseValue($userId);

        $this->assertSame($userId, $result->toString());
    }

    public function testParseValueInvalid(): void
    {
        $this->expectException(\Exception::class);

        new UserIdType()->parseValue('asdf');
    }

    public function testConfig(): void
    {
        $type = new UserIdType();

        $this->assertSame('UserId', $type->name());
        $this->assertSame('A UUID for a User represented as string.', $type->description());
    }

    public function testAliases(): void
    {
        $aliases = UserIdType::getAliases();

        $this->assertSame(['UserId'], $aliases);
    }
}
