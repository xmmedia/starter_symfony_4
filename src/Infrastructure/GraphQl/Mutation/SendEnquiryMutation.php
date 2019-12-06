<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Mutation;

use App\Model\Enquiry\Command\SubmitEnquiry;
use App\Model\Enquiry\EnquiryId;
use App\Model\Enquiry\Name;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\MessageBusInterface;
use Xm\SymfonyBundle\Model\Email;

// @todo test; other updates?
class SendEnquiryMutation implements MutationInterface
{
    /** @var MessageBusInterface */
    private $commandBus;

    public function __construct(MessageBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function __invoke(Argument $args): array
    {
        $this->commandBus->dispatch(
            SubmitEnquiry::with(
                EnquiryId::fromUuid(Uuid::uuid4()),
                Name::fromString($args['enquiry']['name']),
                Email::fromString($args['enquiry']['email']),
                $args['enquiry']['message']
            )
        );

        return [
            'success' => true,
        ];
    }
}
