<?php

declare(strict_types=1);

namespace App\Tests\GraphQl\Mutation\User;

use App\GraphQl\Mutation\User\AdminUserDeleteMutation;
use App\Model\User\Command\AdminDeleteUser;
use App\Tests\BaseTestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class AdminUserDeleteMutationTest extends BaseTestCase
{
    public function testValid(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(AdminDeleteUser::class))
            ->andReturn(new Envelope(new \stdClass()));

        $result = (new AdminUserDeleteMutation($commandBus))($userId);

        $this->assertEquals(['success' => true], $result);
    }
}
