<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Mutation\User;

use App\Exception\FormValidationException;
use App\Form\User\UserRecoverInitiateType;
use App\Model\Email;
use App\Model\User\Command\InitiatePasswordRecovery;
use App\Repository\UserRepository;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class UserRecoverInitiateMutation implements MutationInterface
{
    /** @var MessageBusInterface */
    private $commandBus;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var UserRepository */
    private $userRepo;

    public function __construct(
        MessageBusInterface $commandBus,
        FormFactoryInterface $formFactory,
        UserRepository $userRepo
    ) {
        $this->commandBus = $commandBus;
        $this->formFactory = $formFactory;
        $this->userRepo = $userRepo;
    }

    public function __invoke(Argument $args): array
    {
        $form = $this->formFactory
            ->create(UserRecoverInitiateType::class)
            ->submit(['email' => $args['email']]);

        if (!$form->isValid()) {
            throw FormValidationException::fromForm($form);
        }

        $user = $this->userRepo->findOneByEmail(
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
