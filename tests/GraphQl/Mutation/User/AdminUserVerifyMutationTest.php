<?php

declare(strict_types=1);

namespace App\Tests\GraphQl\Mutation\User;

use App\GraphQl\Mutation\User\AdminUserVerifyMutation;
use App\Model\User\Command\VerifyUserByAdmin;
use App\Tests\BaseTestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class AdminUserVerifyMutationTest extends BaseTestCase
{
    public function testActivate(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(VerifyUserByAdmin::class))
            ->andReturn(new Envelope(new \stdClass()));

        $result = (new AdminUserVerifyMutation($commandBus))($userId);

        $expected = [
            'userId' => $userId,
        ];

        $this->assertEquals($expected, $result);
    }
}
