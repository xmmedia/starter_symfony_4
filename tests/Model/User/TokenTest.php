<?php

declare(strict_types=1);

namespace App\Tests\Model\User;

use App\Model\User\Token;
use Faker;
use PHPUnit\Framework\TestCase;

class TokenTest extends TestCase
{
    public function testFromString(): void
    {
        $faker = Faker\Factory::create();
        $tokenString = $faker->asciify(str_repeat('*', 25));

        $token = Token::fromString($tokenString);

        $this->assertEquals($tokenString, $token->token());
        $this->assertEquals($tokenString, $token->toString());
        $this->assertEquals($tokenString, (string) $token);
    }

    public function testEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Token::fromString('');
    }

    public function testSameValueAs(): void
    {
        $faker = Faker\Factory::create();
        $tokenString = $faker->asciify(str_repeat('*', 25));

        $token1 = Token::fromString($tokenString);
        $token2 = Token::fromString($tokenString);

        $this->assertTrue($token1->sameValueAs($token2));
    }
}
