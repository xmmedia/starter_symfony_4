<?php

declare(strict_types=1);

namespace App\Tests\Projection\User;

use App\Projection\User\UserFilters;
use App\Tests\BaseTestCase;

class UserFiltersTest extends BaseTestCase
{
    private $fields = [
        UserFilters::ACTIVE,
        UserFilters::EMAIL,
        UserFilters::EMAIL_EXACT,
    ];

    public function testFromArrayAllFields(): void
    {
        $faker = $this->faker();

        $active = $faker->boolean();
        $email = $faker->email();
        $emailExact = $faker->email();

        $filters = UserFilters::fromArray([
            UserFilters::ACTIVE      => $active,
            UserFilters::EMAIL       => $email,
            UserFilters::EMAIL_EXACT => $emailExact,
        ]);

        foreach ($this->fields as $field) {
            $this->assertTrue($filters->applied($field), $field.' should be applied, but not.');
            $this->assertEquals($$field, $filters->get($field));
        }
    }

    public function testFromArrayOneField(): void
    {
        $faker = $this->faker();

        $email = $faker->email();

        $filters = UserFilters::fromArray([
            UserFilters::EMAIL => $email,
        ]);

        $this->assertTrue($filters->applied(UserFilters::EMAIL), UserFilters::EMAIL.' should be applied, but not.');
        $this->assertEquals($email, $filters->get(UserFilters::EMAIL));

        $this->assertFalse($filters->applied(UserFilters::ACTIVE));
        $this->assertNull($filters->get(UserFilters::ACTIVE));
    }

    public function testFromArrayEmpty(): void
    {
        $filters = UserFilters::fromArray([]);

        foreach ($this->fields as $field) {
            $this->assertFalse($filters->applied($field));
            $this->assertNull($filters->get($field));
        }
    }

    public function testEmptyNull(): void
    {
        $filters = UserFilters::fromArray(null);

        foreach ($this->fields as $field) {
            $this->assertFalse($filters->applied($field));
            $this->assertNull($filters->get($field));
        }
    }

    public function testNullRemoved(): void
    {
        $filters = UserFilters::fromArray([UserFilters::EMAIL => null]);

        $this->assertFalse($filters->applied(UserFilters::EMAIL));
        $this->assertNull($filters->get(UserFilters::EMAIL));
    }

    public function testEmptyStringRemoved(): void
    {
        $filters = UserFilters::fromArray([UserFilters::EMAIL => '']);

        $this->assertFalse($filters->applied(UserFilters::EMAIL));
        $this->assertNull($filters->get(UserFilters::EMAIL));
    }

    public function testFalseNotRemoved(): void
    {
        $filters = UserFilters::fromArray([UserFilters::EMAIL => false]);

        $this->assertTrue($filters->applied(UserFilters::EMAIL));
        $this->assertFalse($filters->get(UserFilters::EMAIL));
    }

    public function testZeroIntNotRemoved(): void
    {
        $filters = UserFilters::fromArray([UserFilters::EMAIL => 0]);

        $this->assertTrue($filters->applied(UserFilters::EMAIL));
        $this->assertEquals(0, $filters->get(UserFilters::EMAIL));
    }

    public function testZeroStringNotRemoved(): void
    {
        $filters = UserFilters::fromArray([UserFilters::EMAIL => '0']);

        $this->assertTrue($filters->applied(UserFilters::EMAIL));
        $this->assertEquals('0', $filters->get(UserFilters::EMAIL));
    }

    public function testInvalidField(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        UserFilters::fromArray(['test' => 'test']);
    }
}
