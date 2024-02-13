<?php

declare(strict_types=1);

namespace App\Tests\Projection\User;

use App\Projection\User\UserFilterQueryBuilder;
use App\Projection\User\UserFilters;
use App\Tests\BaseTestCase;

class UserFilterQueryBuilderTest extends BaseTestCase
{
    /**
     * @var array<string|array>
     */
    private array $defaultParts = [
        'join'           => '',
        'where'          => '1',
        'order'          => 'u.email ASC, u.first_name ASC, u.last_name ASC',
        'parameters'     => [],
        'parameterTypes' => [],
    ];

    public function testQueryNoSeparator(): void
    {
        $filters = UserFilters::fromArray([
            UserFilters::Q => 'name',
        ]);

        $result = (new UserFilterQueryBuilder())->queryParts($filters);

        $expected = $this->defaultParts;
        $expected['where'] = '1 AND (u.email LIKE :q0 OR u.first_name LIKE :q0 OR u.last_name LIKE :q0)';
        $expected['parameters']['q0'] = '%name%';

        $this->assertEquals($expected, $result);
    }

    public function testQueryWithSeparator(): void
    {
        $filters = UserFilters::fromArray([
            UserFilters::Q => 'name1 name2',
        ]);

        $result = (new UserFilterQueryBuilder())->queryParts($filters);

        $expected = $this->defaultParts;
        $expected['where'] = '1 AND (u.email LIKE :q0 OR u.first_name LIKE :q0 OR u.last_name LIKE :q0 OR u.email LIKE :q1 OR u.first_name LIKE :q1 OR u.last_name LIKE :q1)';
        $expected['parameters']['q0'] = '%name1%';
        $expected['parameters']['q1'] = '%name2%';

        $this->assertEquals($expected, $result);
    }

    public function testQueryEmpty(): void
    {
        $filters = UserFilters::fromArray([
            UserFilters::Q => '',
        ]);

        $this->assertEquals($this->defaultParts, (new UserFilterQueryBuilder())->queryParts($filters));
    }

    public function testEmail(): void
    {
        $filters = UserFilters::fromArray([
            UserFilters::EMAIL => 'email@email.com',
        ]);

        $result = (new UserFilterQueryBuilder())->queryParts($filters);

        $expected = $this->defaultParts;
        $expected['where'] = '1 AND u.email LIKE :email';
        $expected['parameters']['email'] = '%email@email.com%';

        $this->assertEquals($expected, $result);
    }

    public function testEmailEmpty(): void
    {
        $filters = UserFilters::fromArray([
            UserFilters::EMAIL => '',
        ]);

        $this->assertEquals($this->defaultParts, (new UserFilterQueryBuilder())->queryParts($filters));
    }

    public function testEmailExact(): void
    {
        $filters = UserFilters::fromArray([
            UserFilters::EMAIL_EXACT => 'email@email.com',
        ]);

        $result = (new UserFilterQueryBuilder())->queryParts($filters);

        $expected = $this->defaultParts;
        $expected['where'] = '1 AND u.email LIKE :email';
        $expected['parameters']['email'] = 'email@email.com';

        $this->assertEquals($expected, $result);
    }

    public function testEmailExactEmpty(): void
    {
        $filters = UserFilters::fromArray([
            UserFilters::EMAIL_EXACT => '',
        ]);

        $this->assertEquals($this->defaultParts, (new UserFilterQueryBuilder())->queryParts($filters));
    }

    public function testActive(): void
    {
        $filters = UserFilters::fromArray([
            UserFilters::ACTIVE => true,
        ]);

        $result = (new UserFilterQueryBuilder())->queryParts($filters);

        $expected = $this->defaultParts;
        $expected['where'] = '1 AND u.active = true AND u.verified = true';

        $this->assertEquals($expected, $result);
    }

    public function testInactive(): void
    {
        $filters = UserFilters::fromArray([
            UserFilters::ACTIVE => false,
        ]);

        $result = (new UserFilterQueryBuilder())->queryParts($filters);

        $expected = $this->defaultParts;
        $expected['where'] = '1 AND (u.active = false OR u.verified = false)';

        $this->assertEquals($expected, $result);
    }

    public function testAccountStatusActive(): void
    {
        $filters = UserFilters::fromArray([
            UserFilters::ACCOUNT_STATUS => 'ACTIVE',
        ]);

        $result = (new UserFilterQueryBuilder())->queryParts($filters);

        $expected = $this->defaultParts;
        $expected['where'] = '1 AND u.active = true AND u.verified = true';

        $this->assertEquals($expected, $result);
    }

    public function testAccountStatusInactive(): void
    {
        $filters = UserFilters::fromArray([
            UserFilters::ACCOUNT_STATUS => 'INACTIVE',
        ]);

        $result = (new UserFilterQueryBuilder())->queryParts($filters);

        $expected = $this->defaultParts;
        $expected['where'] = '1 AND (u.active = false OR u.verified = false)';

        $this->assertEquals($expected, $result);
    }

    public function testAccountStatusAll(): void
    {
        $filters = UserFilters::fromArray([
            UserFilters::ACCOUNT_STATUS => 'ALL',
        ]);

        $result = (new UserFilterQueryBuilder())->queryParts($filters);

        $expected = $this->defaultParts;
        $expected['where'] = '1';

        $this->assertEquals($expected, $result);
    }

    public function testRoles(): void
    {
        $filters = UserFilters::fromArray([
            UserFilters::ROLES => ['ROLE_ADMIN'],
        ]);

        $result = (new UserFilterQueryBuilder())->queryParts($filters);

        $expected = $this->defaultParts;
        $expected['where'] = '1 AND (JSON_CONTAINS(u.roles, :role0) = 1)';
        $expected['parameters']['role0'] = '"ROLE_ADMIN"';

        $this->assertEquals($expected, $result);
    }

    public function testRolesMultiple(): void
    {
        $filters = UserFilters::fromArray([
            UserFilters::ROLES => ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'],
        ]);

        $result = (new UserFilterQueryBuilder())->queryParts($filters);

        $expected = $this->defaultParts;
        $expected['where'] = '1 AND (JSON_CONTAINS(u.roles, :role0) = 1 OR JSON_CONTAINS(u.roles, :role1) = 1)';
        $expected['parameters']['role0'] = '"ROLE_ADMIN"';
        $expected['parameters']['role1'] = '"ROLE_SUPER_ADMIN"';

        $this->assertEquals($expected, $result);
    }

    public function testRolesEmpty(): void
    {
        $filters = UserFilters::fromArray([
            UserFilters::ROLES => [],
        ]);

        $this->assertEquals($this->defaultParts, (new UserFilterQueryBuilder())->queryParts($filters));
    }
}
