<?php

declare(strict_types=1);

namespace App\Tests\Model;

use App\Model\Date;
use App\Util\Json;
use Faker;
use PHPUnit\Framework\TestCase;

class DateTest extends TestCase
{
    /**
     * @dataProvider dateStringProvider
     */
    public function testFromStringAndToString(
        string $string,
        string $expected,
        string $timezone
    ): void {
        $date = Date::fromString($string);

        $this->assertEquals($expected, $date->format(\DateTime::ISO8601));
        $this->assertEquals($timezone, $date->date()->timezone->getName());
    }

    public function dateStringProvider(): \Generator
    {
        $faker = Faker\Factory::create();
        $max = '+5 years';

        $str = $faker->iso8601($max);
        yield [$str, $str, '+00:00'];

        $str = $faker->date('Y-m-d', $max);
        yield [$str, $str.'T00:00:00+0000', 'UTC'];
    }

    public function testNow(): void
    {
        $now = new \DateTimeImmutable();
        $date = Date::now();

        $this->assertEquals(
            $now->format(\DateTime::ISO8601),
            $date->date()->format(\DateTime::ISO8601)
        );
        $this->assertEquals('UTC', $date->date()->timezone->getName());
    }

    /**
     * @dataProvider dateTimeProvider
     */
    public function testFromDateTime(\DateTimeInterface $dateTime): void
    {
        $date = Date::fromDateTime($dateTime);

        $this->assertEquals(
            $dateTime->format(\DateTime::ISO8601),
            $date->date()->format(\DateTime::ISO8601)
        );
        $this->assertEquals('UTC', $date->date()->timezone->getName());
    }

    public function dateTimeProvider(): \Generator
    {
        yield [new \DateTime()];
        yield [new \DateTimeImmutable()];
    }

    public function testFormat(): void
    {
        $dateString = '2000-01-01';
        $date = Date::fromString($dateString);

        $this->assertEquals($dateString, $date->format('Y-m-d'));
    }

    public function testToString(): void
    {
        $dateString = '2000-01-01';
        $date = Date::fromString($dateString);

        $this->assertEquals($dateString, $date->toString());
        $this->assertEquals($dateString, (string) $date);
    }

    /**
     * @dataProvider jsonProvider
     */
    public function testJsonSerialize(Date $date, string $expected): void
    {
        $this->assertEquals($expected, Json::encode($date));
    }

    public function jsonProvider(): \Generator
    {
        yield [Date::fromString('2000-01-01'), '"2000-01-01T00:00:00.000000Z"'];

        $date = Date::now();
        yield [$date, '"'.$date->format('Y-m-d\TH:i:s.u\Z').'"'];
    }

    public function testSameValueAs(): void
    {
        $date1 = Date::fromString('2000-01-01');
        $date2 = Date::fromString('2000-01-01');

        $this->assertTrue($date1->sameValueAs($date2));
    }

    public function testInvalid(): void
    {
        $this->expectException(\Exception::class);

        Date::fromString('asdf');
    }
}
