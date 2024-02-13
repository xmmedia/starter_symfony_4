<?php

declare(strict_types=1);

namespace App\Tests\Model\User;

use App\Model\User\UserData;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\FakeVo;

class UserDataTest extends BaseTestCase
{
    public function testFromArray(): void
    {
        $faker = $this->faker();
        $phoneNumber = $faker->phoneNumberVo();

        $userData = UserData::fromArray([
            'phoneNumber' => $phoneNumber->national(),
        ]);

        $this->assertSameValueAs($phoneNumber, $userData->phoneNumber());

        $expected = [
            'phoneNumber' => $phoneNumber->toArray(),
        ];
        $this->assertSame($expected, $userData->toArray());
    }

    public function testFromArrayNulls(): void
    {
        $userData = UserData::fromArray([
            'phoneNumber' => null,
        ]);

        $this->assertNull($userData->phoneNumber());

        $expected = [
            'phoneNumber' => null,
        ];
        $this->assertSame($expected, $userData->toArray());
    }

    public function testSameValueAs(): void
    {
        $faker = $this->faker();
        $phoneNumber = $faker->phoneNumberVo();

        $userData1 = UserData::fromArray([
            'phoneNumber' => $phoneNumber->national(),
        ]);
        $userData2 = UserData::fromArray([
            'phoneNumber' => $phoneNumber->national(),
        ]);

        $this->assertTrue($userData1->sameValueAs($userData2));
    }

    public function testSameValueAsDiffPhone(): void
    {
        $faker = $this->faker();

        $userData1 = UserData::fromArray([
            'phoneNumber' => $faker->phoneNumberVo()->national(),
        ]);
        $userData2 = UserData::fromArray([
            'phoneNumber' => $faker->phoneNumberVo()->national(),
        ]);

        $this->assertFalse($userData1->sameValueAs($userData2));
    }

    public function testSameValueAsDiffClass(): void
    {
        $faker = $this->faker();

        $userData = UserData::fromArray([
            'phoneNumber' => $faker->phoneNumberVo()->national(),
        ]);

        $this->assertFalse($userData->sameValueAs(FakeVo::create()));
    }
}
