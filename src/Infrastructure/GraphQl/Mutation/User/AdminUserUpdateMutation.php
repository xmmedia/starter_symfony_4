<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Mutation\User;

use App\Exception\FormValidationException;
use App\Form\User\AdminUserUpdateType;
use App\Model\Email;
use App\Model\User\Command\AdminChangePassword;
use App\Model\User\Command\AdminUpdateUser;
use App\Model\User\Name;
use App\Model\User\UserId;
use App\Security\PasswordEncoder;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Role\Role;

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

        $userId = UserId::fromString($form->getData()['userId']);

        $this->commandBus->dispatch(AdminUpdateUser::with(
            $userId,
            ...$this->transformData($form)
        ));

        if ($form->getData()['changePassword']) {
            $this->commandBus->dispatch(
                AdminChangePassword::with(
                    $userId,
                    ...$this->transformChangePasswordData($form)
                )
            );
        }

        return [
            'userId' => $userId,
        ];
    }

    private function transformData(FormInterface $form): array
    {
        $formData = $form->getData();

        return [
            Email::fromString($formData['email']),
            new Role($formData['role']),
            Name::fromString($formData['firstName']),
            Name::fromString($formData['lastName']),
        ];
    }

    private function transformChangePasswordData(FormInterface $form): array
    {
        $formData = $form->getData();

        $encodedPassword = ($this->passwordEncoder)(
            new Role($formData['role']),
            $formData['password']
        );

        return [
            $encodedPassword,
        ];
    }
}
