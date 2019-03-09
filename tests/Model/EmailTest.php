<?php

declare(strict_types=1);

namespace App\Tests\Model;

use App\Model\Email;
use App\Tests\BaseTestCase;

class EmailTest extends BaseTestCase
{
    public function testFromString(): void
    {
        $faker = $this->faker();

        $email = $faker->email;
        $name = $faker->name;

        $vo = Email::fromString($email, $name);

        $this->assertEquals($email, $vo->email());
        $this->assertEquals($email, $vo->toString());
        $this->assertEquals($email, (string) $vo);
        $this->assertEquals($name, $vo->name());
    }

    public function testFromStringWithoutName(): void
    {
        $faker = $this->faker();

        $email = $faker->email;

        $vo = Email::fromString($email);

        $this->assertEquals($email, $vo->toString());
        $this->assertEquals($email, (string) $vo);
        $this->assertNull($vo->name());
    }

    public function testNullName(): void
    {
        $faker = $this->faker();

        $email = $faker->email;

        $vo = Email::fromString($email, null);

        $this->assertEquals($email, $vo->toString());
        $this->assertEquals($email, (string) $vo);
        $this->assertNull($vo->name());
    }

    public function testEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Email::fromString('');
    }

    public function testInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Email::fromString('asdf');
    }

    public function testFromStringWithNameMethod(): void
    {
        $vo = Email::fromString('email@email.com', 'Name');

        $this->assertEquals('Name <email@email.com>', $vo->withName());
    }

    public function testFromStringWithNameMethodWithoutName(): void
    {
        $vo = Email::fromString('email@email.com');

        $this->assertEquals('email@email.com', $vo->withName());
    }

    public function testSameAs(): void
    {
        $vo1 = Email::fromString('email@email.com');
        $vo2 = Email::fromString('email@email.com');

        $this->assertTrue($vo1->sameValueAs($vo2));
    }

    public function testSameAsCapitals(): void
    {
        $vo1 = Email::fromString('eMail@email.com');
        $vo2 = Email::fromString('email@eMail.com');

        $this->assertTrue($vo1->sameValueAs($vo2));
    }

    public function testSameAsDiffClass(): void
    {
        $vo1 = Email::fromString('eMail@email.com');
        $vo2 = Email::fromString('email@eMail.com');

        $this->assertTrue($vo1->sameValueAs($vo2));
    }
}
