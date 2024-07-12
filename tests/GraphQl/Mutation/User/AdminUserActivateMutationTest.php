<?php

declare(strict_types=1);

namespace App\Tests\GraphQl\Mutation\User;

use App\GraphQl\Mutation\User\AdminUserActivateMutation;
use App\Model\User\Command\ActivateUserByAdmin;
use App\Model\User\Command\DeactivateUserByAdmin;
use App\Tests\BaseTestCase;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class AdminUserActivateMutationTest extends BaseTestCase
{
    public function testActivate(): void
    {
        $faker = $this->faker();

        $data = [
            'userId' => $faker->userId(),
            'action' => 'activate',
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(ActivateUserByAdmin::class))
            ->andReturn(new Envelope(new \stdClass()));

        $args = new Argument([
            'user' => $data,
        ]);

        $result = (new AdminUserActivateMutation(
            $commandBus,
        ))($args);

        $expected = [
            'userId' => $data['userId'],
        ];

        $this->assertEquals($expected, $result);
    }

    public function testDeactivate(): void
    {
        $faker = $this->faker();

        $data = [
            'userId' => $faker->userId(),
            'action' => 'deactivate',
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(DeactivateUserByAdmin::class))
            ->andReturn(new Envelope(new \stdClass()));

        $args = new Argument([
            'user' => $data,
        ]);

        $result = (new AdminUserActivateMutation(
            $commandBus,
        ))($args);

        $expected = [
            'userId' => $data['userId'],
        ];

        $this->assertEquals($expected, $result);
    }

    public function testInvalidAction(): void
    {
        $faker = $this->faker();

        $data = [
            'userId' => $faker->userId(),
            'action' => 'asdf',
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);

        $args = new Argument([
            'user' => $data,
        ]);

        $this->expectException(UserError::class);

        (new AdminUserActivateMutation(
            $commandBus,
        ))($args);
    }
}
