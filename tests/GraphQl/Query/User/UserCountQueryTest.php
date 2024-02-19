<?php

declare(strict_types=1);

namespace App\Tests\GraphQl\Query\User;

use App\GraphQl\Query\User\UserCountQuery;
use App\Projection\User\UserFinder;
use App\Tests\BaseTestCase;

class UserCountQueryTest extends BaseTestCase
{
    public function test(): void
    {
        $count = $this->faker()->randomNumber();

        $userFinder = \Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('countByFilters')
            ->once()
            ->andReturn($count);

        $result = (new UserCountQuery($userFinder))([]);

        $this->assertSame($count, $result);
    }
}
