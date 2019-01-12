<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Mutation;

use App\Exception\FormValidationException;
use App\Form\AdminUserCreateType;
use App\Model\User\Command\AdminCreateUser;
use App\Security\PasswordEncoder;
use App\Security\TokenGenerator;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Form\FormFactoryInterface;
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

        $userId = $form->getData()['id'];

        if (!$form->getData()['setPassword']) {
            $password = ($this->tokenGenerator)()->toString();
        } else {
            $password = $form->getData()['password'];
        }

        $encodedPassword = ($this->passwordEncoder)($form->getData()['role'], $password);

        $this->commandBus->dispatch(AdminCreateUser::with(
            $userId,
            $form->getData()['email'],
            $encodedPassword,
            $form->getData()['role'],
            $form->getData()['active'],
            $form->getData()['firstName'],
            $form->getData()['lastName'],
            $form->getData()['sendInvite']
        ));

        return [
            'id'     => $userId,
            'email'  => $form->getData()['email'],
            'active' => $form->getData()['active'],
        ];
    }
}
