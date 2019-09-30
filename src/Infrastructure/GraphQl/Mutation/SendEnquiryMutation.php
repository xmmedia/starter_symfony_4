<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Mutation;

use App\Exception\FormValidationException;
use App\Form\EnquiryType;
use App\Model\Enquiry\Command\SubmitEnquiry;
use App\Model\Enquiry\EnquiryId;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Xm\SymfonyBundle\Model\Email;

// @todo test; other updates?
class SendEnquiryMutation implements MutationInterface
{
    /** @var MessageBusInterface */
    private $commandBus;

    /** @var FormFactoryInterface */
    private $formFactory;

    public function __construct(
        MessageBusInterface $commandBus,
        FormFactoryInterface $formFactory
    ) {
        $this->commandBus = $commandBus;
        $this->formFactory = $formFactory;
    }

    public function __invoke(Argument $args): array
    {
        $form = $this->formFactory
            ->create(EnquiryType::class)
            ->submit($args['enquiry']);

        if (!$form->isValid()) {
            throw FormValidationException::fromForm($form, 'enquiry');
        }

        $this->commandBus->dispatch(SubmitEnquiry::with(
            EnquiryId::fromUuid(Uuid::uuid4()),
            $form->getData()['name'],
            Email::fromString($form->getData()['email']),
            $form->getData()['message']
        ));

        return [
            'success' => true,
        ];
    }
}
