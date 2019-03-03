<?php

declare(strict_types=1);

namespace App\Tests\Model\User;

use App\Model\User\Name;
use App\Tests\BaseTestCase;
use App\Util\Json;

class NameTest extends BaseTestCase
{
    public function testFromString(): void
    {
        $faker = $this->faker();
        $nameString = $faker->name;

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
        $nameString = $faker->name;

        $name1 = Name::fromString($nameString);
        $name2 = Name::fromString($nameString);

        $this->assertTrue($name1->sameValueAs($name2));
    }
}
