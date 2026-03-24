<?php

declare(strict_types=1);

namespace App\Tests\Projection\AuthLog;

use App\Projection\AuthLog\AuthLogFilters;
use App\Tests\BaseTestCase;

class AuthLogFiltersTest extends BaseTestCase
{
    /**
     * @var string[]
     */
    private array $fields = [
        AuthLogFilters::EVENT_TYPES,
        AuthLogFilters::DATE_FROM,
        AuthLogFilters::DATE_TO,
        AuthLogFilters::Q,
        AuthLogFilters::OFFSET,
    ];

    public function testFromArrayAllFields(): void
    {
        $faker = $this->faker();

        $eventTypes = ['login', 'login_failed'];
        $dateFrom = $faker->dateTime()->format('Y-m-d');
        $dateTo = $faker->dateTime()->format('Y-m-d');
        $q = $faker->string(5);
        $offset = $faker->numberBetween(1, 200);

        $filters = AuthLogFilters::fromArray([
            AuthLogFilters::EVENT_TYPES => $eventTypes,
            AuthLogFilters::DATE_FROM   => $dateFrom,
            AuthLogFilters::DATE_TO     => $dateTo,
            AuthLogFilters::Q           => $q,
            AuthLogFilters::OFFSET      => $offset,
        ]);

        $this->assertTrue($filters->applied(AuthLogFilters::EVENT_TYPES));
        $this->assertEquals($eventTypes, $filters->get(AuthLogFilters::EVENT_TYPES));

        $this->assertTrue($filters->applied(AuthLogFilters::DATE_FROM));
        $this->assertEquals($dateFrom, $filters->get(AuthLogFilters::DATE_FROM));

        $this->assertTrue($filters->applied(AuthLogFilters::DATE_TO));
        $this->assertEquals($dateTo, $filters->get(AuthLogFilters::DATE_TO));

        $this->assertTrue($filters->applied(AuthLogFilters::Q));
        $this->assertEquals($q, $filters->get(AuthLogFilters::Q));

        $this->assertTrue($filters->applied(AuthLogFilters::OFFSET));
        $this->assertEquals($offset, $filters->get(AuthLogFilters::OFFSET));
    }

    public function testFromArrayOneField(): void
    {
        $faker = $this->faker();

        $q = $faker->string(5);

        $filters = AuthLogFilters::fromArray([
            AuthLogFilters::Q => $q,
        ]);

        $this->assertTrue($filters->applied(AuthLogFilters::Q));
        $this->assertEquals($q, $filters->get(AuthLogFilters::Q));

        $this->assertFalse($filters->applied(AuthLogFilters::EVENT_TYPES));
        $this->assertNull($filters->get(AuthLogFilters::EVENT_TYPES));
    }

    public function testFromArrayEmpty(): void
    {
        $filters = AuthLogFilters::fromArray([]);

        foreach ($this->fields as $field) {
            $this->assertFalse($filters->applied($field));
            $this->assertNull($filters->get($field));
        }
    }

    public function testEmptyNull(): void
    {
        $filters = AuthLogFilters::fromArray(null);

        foreach ($this->fields as $field) {
            $this->assertFalse($filters->applied($field));
            $this->assertNull($filters->get($field));
        }
    }

    public function testNullRemoved(): void
    {
        $filters = AuthLogFilters::fromArray([AuthLogFilters::Q => null]);

        $this->assertFalse($filters->applied(AuthLogFilters::Q));
        $this->assertNull($filters->get(AuthLogFilters::Q));
    }

    public function testEmptyStringRemoved(): void
    {
        $filters = AuthLogFilters::fromArray([AuthLogFilters::Q => '']);

        $this->assertFalse($filters->applied(AuthLogFilters::Q));
        $this->assertNull($filters->get(AuthLogFilters::Q));
    }

    public function testInvalidField(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        AuthLogFilters::fromArray(['invalid' => 'test']);
    }
}
