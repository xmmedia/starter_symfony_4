<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Mutation\User;

use App\Exception\FormValidationException;
use App\Form\User\UserProfileType;
use App\Model\Email;
use App\Model\User\Command\UpdateUserProfile;
use App\Model\User\Name;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;

class UserUpdateProfileMutation implements MutationInterface
{
    /** @var MessageBusInterface */
    private $commandBus;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var Security */
    private $security;

    public function __construct(
        MessageBusInterface $commandBus,
        FormFactoryInterface $formFactory,
        Security $security
    ) {
        $this->commandBus = $commandBus;
        $this->formFactory = $formFactory;
        $this->security = $security;
    }

    public function __invoke(Argument $args): array
    {
        $form = $this->formFactory
            ->create(UserProfileType::class)
            ->submit($args['user']);

        if (!$form->isValid()) {
            throw FormValidationException::fromForm($form, 'user');
        }

        $this->commandBus->dispatch(
            UpdateUserProfile::with(
                $this->security->getUser()->userId(),
                ...$this->transformData($form)
            )
        );

        return [
            'userId' => $this->security->getUser()->userId(),
        ];
    }

    private function transformData(FormInterface $form): array
    {
        $formData = $form->getData();

        return [
            Email::fromString($formData['email']),
            Name::fromString($formData['firstName']),
            Name::fromString($formData['lastName']),
        ];
    }
}
