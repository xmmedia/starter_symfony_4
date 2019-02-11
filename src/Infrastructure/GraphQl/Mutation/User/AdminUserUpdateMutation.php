<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Mutation\User;

use App\Exception\FormValidationException;
use App\Form\User\AdminUserUpdateType;
use App\Model\User\Command\AdminChangePassword;
use App\Model\User\Command\AdminUpdateUser;
use App\Model\User\UserId;
use App\Security\PasswordEncoder;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class AdminUserUpdateMutation implements MutationInterface
{
    /** @var MessageBusInterface */
    private $commandBus;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var PasswordEncoder */
    private $passwordEncoder;

    public function __construct(
        MessageBusInterface $commandBus,
        FormFactoryInterface $formFactory,
        PasswordEncoder $passwordEncoder
    ) {
        $this->commandBus = $commandBus;
        $this->formFactory = $formFactory;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function __invoke(Argument $args): array
    {
        $form = $this->formFactory
            ->create(AdminUserUpdateType::class)
            ->submit($args['user']);

        if (!$form->isValid()) {
            throw FormValidationException::fromForm($form, 'user');
        }

        /** @var UserId $userId */
        $userId = $form->getData()['userId'];

        $this->commandBus->dispatch(AdminUpdateUser::with(
            $userId,
            $form->getData()['email'],
            $form->getData()['role'],
            $form->getData()['firstName'],
            $form->getData()['lastName']
        ));

        if ($form->getData()['changePassword']) {
            $encodedPassword = ($this->passwordEncoder)(
                $form->getData()['role'],
                $form->getData()['password']
            );

            $this->commandBus->dispatch(
                AdminChangePassword::with($userId, $encodedPassword)
            );
        }

        return [
            'userId' => $userId,
        ];
    }
}
