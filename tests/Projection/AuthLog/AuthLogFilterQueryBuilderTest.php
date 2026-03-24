<?php

declare(strict_types=1);

namespace App\Tests\Projection\AuthLog;

use App\Projection\AuthLog\AuthLogFilterQueryBuilder;
use App\Projection\AuthLog\AuthLogFilters;
use App\Tests\BaseTestCase;

class AuthLogFilterQueryBuilderTest extends BaseTestCase
{
    /**
     * @var array<string, string|array>
     */
    private array $defaultParts = [
        'join'           => '',
        'where'          => '1',
        'order'          => 'a.occurred_at DESC',
        'parameters'     => [],
        'parameterTypes' => [],
    ];

    public function testNoFilters(): void
    {
        $filters = AuthLogFilters::fromArray([]);

        $this->assertEquals($this->defaultParts, new AuthLogFilterQueryBuilder()->queryParts($filters));
    }

    public function testEventTypesSingle(): void
    {
        $filters = AuthLogFilters::fromArray([
            AuthLogFilters::EVENT_TYPES => ['login'],
        ]);

        $result = new AuthLogFilterQueryBuilder()->queryParts($filters);

        $expected = $this->defaultParts;
        $expected['where'] = '1 AND a.event_type IN (:eventType0)';
        $expected['parameters']['eventType0'] = 'login';

        $this->assertEquals($expected, $result);
    }

    public function testEventTypesMultiple(): void
    {
        $filters = AuthLogFilters::fromArray([
            AuthLogFilters::EVENT_TYPES => ['login', 'login_failed'],
        ]);

        $result = new AuthLogFilterQueryBuilder()->queryParts($filters);

        $expected = $this->defaultParts;
        $expected['where'] = '1 AND a.event_type IN (:eventType0, :eventType1)';
        $expected['parameters']['eventType0'] = 'login';
        $expected['parameters']['eventType1'] = 'login_failed';

        $this->assertEquals($expected, $result);
    }

    public function testDateFrom(): void
    {
        $date = $this->faker()->dateTime()->format('Y-m-d');

        $filters = AuthLogFilters::fromArray([
            AuthLogFilters::DATE_FROM => $date,
        ]);

        $result = new AuthLogFilterQueryBuilder()->queryParts($filters);

        $expected = $this->defaultParts;
        $expected['where'] = '1 AND a.occurred_at >= :dateFrom';
        $expected['parameters']['dateFrom'] = $date;

        $this->assertEquals($expected, $result);
    }

    public function testDateTo(): void
    {
        $date = $this->faker()->dateTime()->format('Y-m-d');

        $filters = AuthLogFilters::fromArray([
            AuthLogFilters::DATE_TO => $date,
        ]);

        $result = new AuthLogFilterQueryBuilder()->queryParts($filters);

        $expected = $this->defaultParts;
        $expected['where'] = '1 AND a.occurred_at <= :dateTo';
        $expected['parameters']['dateTo'] = $date;

        $this->assertEquals($expected, $result);
    }

    public function testQ(): void
    {
        $q = $this->faker()->string(5);

        $filters = AuthLogFilters::fromArray([
            AuthLogFilters::Q => $q,
        ]);

        $result = new AuthLogFilterQueryBuilder()->queryParts($filters);

        $expected = $this->defaultParts;
        $expected['where'] = '1 AND a.email LIKE :q';
        $expected['parameters']['q'] = '%'.$q.'%';

        $this->assertEquals($expected, $result);
    }

    public function testQEmpty(): void
    {
        $filters = AuthLogFilters::fromArray([
            AuthLogFilters::Q => '',
        ]);

        $this->assertEquals($this->defaultParts, new AuthLogFilterQueryBuilder()->queryParts($filters));
    }

    public function testMultipleFilters(): void
    {
        $faker = $this->faker();
        $date = $faker->dateTime()->format('Y-m-d');
        $q = $faker->string(5);

        $filters = AuthLogFilters::fromArray([
            AuthLogFilters::EVENT_TYPES => ['login'],
            AuthLogFilters::DATE_FROM   => $date,
            AuthLogFilters::Q           => $q,
        ]);

        $result = new AuthLogFilterQueryBuilder()->queryParts($filters);

        $expected = $this->defaultParts;
        $expected['where'] = '1 AND a.event_type IN (:eventType0) AND a.occurred_at >= :dateFrom AND a.email LIKE :q';
        $expected['parameters']['eventType0'] = 'login';
        $expected['parameters']['dateFrom'] = $date;
        $expected['parameters']['q'] = '%'.$q.'%';

        $this->assertEquals($expected, $result);
    }
}
