<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Mutation\User;

use App\Model\User\Command\InitiatePasswordRecovery;
use App\Projection\User\UserFinder;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Xm\SymfonyBundle\Exception\FormValidationException;
use Xm\SymfonyBundle\Form\User\UserRecoverInitiateType;
use Xm\SymfonyBundle\Model\Email;

class UserRecoverInitiateMutation implements MutationInterface
{
    /** @var MessageBusInterface */
    private $commandBus;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var UserFinder */
    private $userFinder;

    public function __construct(
        MessageBusInterface $commandBus,
        FormFactoryInterface $formFactory,
        UserFinder $userFinder
    ) {
        $this->commandBus = $commandBus;
        $this->formFactory = $formFactory;
        $this->userFinder = $userFinder;
    }

    public function __invoke(Argument $args): array
    {
        $form = $this->formFactory
            ->create(UserRecoverInitiateType::class)
            ->submit(['email' => $args['email']]);

        if (!$form->isValid()) {
            throw FormValidationException::fromForm($form);
        }

        $user = $this->userFinder->findOneByEmail(
            Email::fromString($form->getData()['email'])
        );

        if (!$user || !$user->active()) {
            throw new UserError('An account with that email cannot be found.', 404);
        }

        $this->commandBus->dispatch(
            InitiatePasswordRecovery::now($user->userId(), $user->email())
        );

        return [
            'success' => true,
        ];
    }
}
