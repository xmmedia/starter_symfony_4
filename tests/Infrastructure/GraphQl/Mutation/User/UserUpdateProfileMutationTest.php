<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\GraphQl\Mutation\User;

use App\Infrastructure\GraphQl\Mutation\User\UserUpdateProfileMutation;
use App\Model\User\Command\UpdateUserProfile;
use App\Tests\BaseTestCase;
use Mockery;
use Overblog\GraphQLBundle\Definition\Argument;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Xm\SymfonyBundle\Exception\FormValidationException;
use Xm\SymfonyBundle\Form\User\UserProfileType;
use Xm\SymfonyBundle\Tests\CanCreateSecurityTrait;

class UserUpdateProfileMutationTest extends BaseTestCase
{
    use CanCreateSecurityTrait;

    public function testValid(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId;
        $data = [
            'email'     => $faker->email,
            'firstName' => $faker->name,
            'lastName'  => $faker->name,
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(UpdateUserProfile::class))
            ->andReturn(new Envelope(new \stdClass()));

        $user = Mockery::mock(UserInterface::class);
        $user->shouldReceive('userId')
            ->once()
            ->andReturn($userId);
        $security = $this->createSecurity($user);

        $args = new Argument([
            'user' => $data,
        ]);

        $result = (new UserUpdateProfileMutation($commandBus, $security))($args);

        $this->assertEquals(['success' => true], $result);
    }
}
