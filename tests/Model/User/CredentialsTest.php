<?php

declare(strict_types=1);

namespace App\Tests\Model\User;

use App\Model\User\Credentials;
use Faker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Csrf\CsrfToken;

class CredentialsTest extends TestCase
{
    public function testBuild(): void
    {
        $faker = Faker\Factory::create();

        $email = $faker->email;
        $password = $faker->password;
        $token = $faker->asciify(str_repeat('*', 25));

        $credentials = Credentials::build($email, $password, $token);

        $this->assertEquals($email, $credentials->email());
        $this->assertEquals($password, $credentials->password());
        $this->assertEquals(
            new CsrfToken(Credentials::CSRF_TOKEN_ID, $token),
            $credentials->csrfToken()
        );
    }

    public function testBuildNulls(): void
    {
        $credentials = Credentials::build(null, null, null);

        $this->assertNull($credentials->email());
        $this->assertNull($credentials->password());
        $this->assertEquals(
            new CsrfToken(Credentials::CSRF_TOKEN_ID, null),
            $credentials->csrfToken()
        );
    }

    public function testSameValueAs(): void
    {
        $faker = Faker\Factory::create();

        $email = $faker->email;
        $password = $faker->password;
        $token = $faker->asciify(str_repeat('*', 25));

        $credentials1 = Credentials::build($email, $password, $token);
        $credentials2 = Credentials::build($email, $password, $token);

        $this->assertTrue($credentials1->sameValueAs($credentials2));
    }

    public function testSameValueAsDiffEmail(): void
    {
        $faker = Faker\Factory::create();

        $password = $faker->password;
        $token = $faker->asciify(str_repeat('*', 25));

        $credentials1 = Credentials::build($faker->email, $password, $token);
        $credentials2 = Credentials::build($faker->email, $password, $token);

        $this->assertFalse($credentials1->sameValueAs($credentials2));
    }

    public function testSameValueAsDiffPassword(): void
    {
        $faker = Faker\Factory::create();

        $email = $faker->email;
        $token = $faker->asciify(str_repeat('*', 25));

        $credentials1 = Credentials::build($email, $faker->password, $token);
        $credentials2 = Credentials::build($email, $faker->password, $token);

        $this->assertFalse($credentials1->sameValueAs($credentials2));
    }

    public function testSameValueAsDiffToken(): void
    {
        $faker = Faker\Factory::create();

        $email = $faker->email;
        $password = $faker->password;

        $credentials1 = Credentials::build(
            $email,
            $password,
            $faker->asciify(str_repeat('*', 25))
        );
        $credentials2 = Credentials::build(
            $email,
            $password,
            $faker->asciify(str_repeat('*', 25))
        );

        $this->assertFalse($credentials1->sameValueAs($credentials2));
    }
}
