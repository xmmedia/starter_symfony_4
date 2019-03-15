<?php

declare(strict_types=1);

namespace App\Tests\Model\User;

use App\Model\User\Credentials;
use App\Tests\BaseTestCase;
use App\Tests\FakeVo;

class CredentialsTest extends BaseTestCase
{
    public function testBuild(): void
    {
        $faker = $this->faker();

        $email = $faker->email;
        $password = $faker->password;

        $credentials = Credentials::build($email, $password);

        $this->assertEquals($email, $credentials->email());
        $this->assertEquals($password, $credentials->password());
    }

    public function testBuildNulls(): void
    {
        $credentials = Credentials::build(null, null);

        $this->assertNull($credentials->email());
        $this->assertNull($credentials->password());
    }

    public function testSameValueAs(): void
    {
        $faker = $this->faker();

        $email = $faker->email;
        $password = $faker->password;

        $credentials1 = Credentials::build($email, $password);
        $credentials2 = Credentials::build($email, $password);

        $this->assertTrue($credentials1->sameValueAs($credentials2));
    }

    public function testSameValueAsDiffEmail(): void
    {
        $faker = $this->faker();

        $password = $faker->password;

        $credentials1 = Credentials::build($faker->email, $password);
        $credentials2 = Credentials::build($faker->email, $password);

        $this->assertFalse($credentials1->sameValueAs($credentials2));
    }

    public function testSameValueAsDiffPassword(): void
    {
        $faker = $this->faker();

        $email = $faker->email;

        $credentials1 = Credentials::build($email, $faker->password);
        $credentials2 = Credentials::build($email, $faker->password);

        $this->assertFalse($credentials1->sameValueAs($credentials2));
    }

    public function testSameValueAsDiffClass(): void
    {
        $faker = $this->faker();

        $credentials = Credentials::build($faker->email, $faker->password);

        $this->assertFalse($credentials->sameValueAs(FakeVo::create()));
    }
}
