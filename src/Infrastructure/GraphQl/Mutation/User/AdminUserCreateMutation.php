<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Mutation\User;

use App\Exception\FormValidationException;
use App\Form\User\AdminUserCreateType;
use App\Model\Email;
use App\Model\User\Command\AdminCreateUser;
use App\Model\User\Name;
use App\Model\User\Role;
use App\Model\User\UserId;
use App\Security\PasswordEncoder;
use App\Security\TokenGenerator;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class AdminUserCreateMutation implements MutationInterface
{
    /** @var MessageBusInterface */
    private $commandBus;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var TokenGenerator */
    private $tokenGenerator;

    /** @var PasswordEncoder */
    private $passwordEncoder;

    public function __construct(
        MessageBusInterface $commandBus,
        FormFactoryInterface $formFactory,
        TokenGenerator $tokenGenerator,
        PasswordEncoder $passwordEncoder
    ) {
        $this->commandBus = $commandBus;
        $this->formFactory = $formFactory;
        $this->tokenGenerator = $tokenGenerator;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function __invoke(Argument $args): array
    {
        $form = $this->formFactory
            ->create(AdminUserCreateType::class)
            ->submit($args['user']);

        if (!$form->isValid()) {
            throw FormValidationException::fromForm($form, 'user');
        }

        $userId = UserId::fromString($form->getData()['userId']);

        $this->commandBus->dispatch(AdminCreateUser::with(
            $userId,
            ...$this->transformData($form)
        ));

        return [
            'userId' => $userId,
            'email'  => $form->getData()['email'],
            'active' => $form->getData()['active'],
        ];
    }

    private function transformData(FormInterface $form): array
    {
        $formData = $form->getData();

        if (!$form->getData()['setPassword']) {
            $password = ($this->tokenGenerator)()->toString();
        } else {
            $password = $formData['password'];
        }

        $role = Role::byValue($formData['role']);

        return [
            Email::fromString($formData['email']),
            ($this->passwordEncoder)($role, $password),
            $role,
            $formData['active'],
            Name::fromString($formData['firstName']),
            Name::fromString($formData['lastName']),
            $formData['sendInvite'],
        ];
    }
}
