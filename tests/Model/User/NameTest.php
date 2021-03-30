<?php

declare(strict_types=1);

namespace App\Tests\Model\User;

use App\Model\User\Name;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\FakeVo;
use Xm\SymfonyBundle\Util\Json;

class NameTest extends BaseTestCase
{
    public function testFromString(): void
    {
        $faker = $this->faker();
        $nameString = $faker->name();

        $name = Name::fromString($nameString);

        $this->assertEquals($nameString, $name->name());
        $this->assertEquals($nameString, $name->toString());
        $this->assertEquals($nameString, (string) $name);
        $this->assertEquals('"'.$nameString.'"', Json::encode($name));
    }

    public function testTooShort(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Name::fromString('A');
    }

    public function testEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Name::fromString('');
    }

    public function testTooLong(): void
    {
        $faker = $this->faker();

        $this->expectException(\InvalidArgumentException::class);

        Name::fromString($faker->asciify(str_repeat('*', 51)));
    }

    public function testSameValueAs(): void
    {
        $faker = $this->faker();
        $nameString = $faker->name();

        $name1 = Name::fromString($nameString);
        $name2 = Name::fromString($nameString);

        $this->assertTrue($name1->sameValueAs($name2));
    }

    public function testSameValueAsFalse(): void
    {
        $faker = $this->faker();

        $name1 = Name::fromString($faker->unique()->name());
        $name2 = Name::fromString($faker->unique()->name());

        $this->assertFalse($name1->sameValueAs($name2));
    }

    public function testSameValueAsDiffClass(): void
    {
        $faker = $this->faker();

        $name = Name::fromString($faker->name());

        $this->assertFalse($name->sameValueAs(FakeVo::create()));
    }
}
