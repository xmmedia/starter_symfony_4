<?php

declare(strict_types=1);

namespace App\Tests\Model\User;

use App\Model\User\Credentials;
use Faker;
use PHPUnit\Framework\TestCase;

class CredentialsTest extends TestCase
{
    public function testBuild(): void
    {
        $faker = Faker\Factory::create();

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
        $faker = Faker\Factory::create();

        $email = $faker->email;
        $password = $faker->password;

        $credentials1 = Credentials::build($email, $password);
        $credentials2 = Credentials::build($email, $password);

        $this->assertTrue($credentials1->sameValueAs($credentials2));
    }

    public function testSameValueAsDiffEmail(): void
    {
        $faker = Faker\Factory::create();

        $password = $faker->password;

        $credentials1 = Credentials::build($faker->email, $password);
        $credentials2 = Credentials::build($faker->email, $password);

        $this->assertFalse($credentials1->sameValueAs($credentials2));
    }

    public function testSameValueAsDiffPassword(): void
    {
        $faker = Faker\Factory::create();

        $email = $faker->email;

        $credentials1 = Credentials::build($email, $faker->password);
        $credentials2 = Credentials::build($email, $faker->password);

        $this->assertFalse($credentials1->sameValueAs($credentials2));
    }
}
