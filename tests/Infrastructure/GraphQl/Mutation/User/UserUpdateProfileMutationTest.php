<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\GraphQl\Mutation\User;

use App\Exception\FormValidationException;
use App\Form\User\UserProfileType;
use App\Infrastructure\GraphQl\Mutation\User\UserUpdateProfileMutation;
use App\Model\Email;
use App\Model\User\Command\UpdateUserProfile;
use App\Model\User\Name;
use App\Tests\BaseTestCase;
use App\Tests\CanCreateSecurityTrait;
use Mockery;
use Overblog\GraphQLBundle\Definition\Argument;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\User\UserInterface;

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
        $transformedData = [
            'email'     => Email::fromString($data['email']),
            'firstName' => Name::fromString($data['firstName']),
            'lastName'  => Name::fromString($data['lastName']),
        ] + $data;

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(UpdateUserProfile::class))
            ->andReturn(new Envelope(new \stdClass()));

        $form = Mockery::mock(FormInterface::class);
        $form->shouldReceive('submit')
            ->once()
            ->with(Mockery::type('array'))
            ->andReturnSelf();
        $form->shouldReceive('isValid')
            ->once()
            ->andReturnTrue();
        $form->shouldReceive('getData')
            ->andReturn($transformedData);
        $formFactory = Mockery::mock(FormFactoryInterface::class);
        $formFactory->shouldReceive('create')
            ->with(UserProfileType::class)
            ->andReturn($form);

        $user = Mockery::mock(UserInterface::class);
        $user->shouldReceive('userId')
            ->atLeast()
            ->times(2)
            ->andReturn($userId);
        $security = $this->createSecurity($user);

        $args = new Argument([
            'user' => $data,
        ]);

        $result = (new UserUpdateProfileMutation(
            $commandBus,
            $formFactory,
            $security
        ))($args);

        $expected = [
            'userId' => $userId->toString(),
        ];

        $this->assertEquals($expected, $result);
    }

    public function testInvalid(): void
    {
        $commandBus = Mockery::mock(MessageBusInterface::class);

        $form = Mockery::mock(FormInterface::class);
        $form->shouldReceive('submit')
            ->once()
            ->with(Mockery::type('array'))
            ->andReturnSelf();
        $form->shouldReceive('isValid')
            ->once()
            ->andReturnFalse();
        $formFactory = Mockery::mock(FormFactoryInterface::class);
        $formFactory->shouldReceive('create')
            ->with(UserProfileType::class)
            ->andReturn($form);

        $user = Mockery::mock(UserInterface::class);
        $security = $this->createSecurity($user);

        $args = new Argument([
            'user' => [],
        ]);

        $this->expectException(FormValidationException::class);

        (new UserUpdateProfileMutation(
            $commandBus,
            $formFactory,
            $security
        ))($args);
    }
}
