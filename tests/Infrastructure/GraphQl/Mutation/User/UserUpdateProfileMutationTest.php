<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\GraphQl\Mutation\User;

use App\Entity\User;
use App\Infrastructure\GraphQl\Mutation\User\UserUpdateProfileMutation;
use App\Model\User\Command\UpdateUserProfile;
use App\Security\Security;
use App\Tests\BaseTestCase;
use Overblog\GraphQLBundle\Definition\Argument;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class UserUpdateProfileMutationTest extends BaseTestCase
{
    public function testValid(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId();
        $data = [
            'email'     => $faker->email(),
            'firstName' => $faker->name(),
            'lastName'  => $faker->name(),
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(UpdateUserProfile::class))
            ->andReturn(new Envelope(new \stdClass()));

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->once()
            ->andReturn($userId);
        $security = \Mockery::mock(Security::class);
        $security->shouldReceive('getUser')
            ->once()
            ->andReturn($user);

        $args = new Argument([
            'user' => $data,
        ]);

        $result = (new UserUpdateProfileMutation($commandBus, $security))($args);

        $this->assertEquals(['success' => true], $result);
    }
}
