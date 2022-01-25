<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\UserToken;
use App\Model\User\Token;
use App\Tests\BaseTestCase;

class UserTokenTest extends BaseTestCase
{
    public function testToken(): void
    {
        $faker = $this->faker();

        $string = $faker->string(25);

        $token = new UserToken();
        $reflection = new \ReflectionClass(UserToken::class);
        $property = $reflection->getProperty('token');
        $property->setAccessible(true);
        $property->setValue($token, $string);

        $this->assertEquals($string, $token->token()->toString());
        $this->assertSameValueAs(Token::fromString($string), $token->token());
    }

    public function testGeneratedAt(): void
    {
        $faker = $this->faker();

        $datetime = \DateTimeImmutable::createFromMutable($faker->dateTime());

        $token = new UserToken();
        $reflection = new \ReflectionClass(UserToken::class);
        $property = $reflection->getProperty('generatedAt');
        $property->setAccessible(true);
        $property->setValue($token, $datetime);

        $this->assertEquals($datetime, $token->generatedAt());
    }
}
