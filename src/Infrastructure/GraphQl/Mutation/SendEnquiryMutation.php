<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Mutation;

use App\Model\Enquiry\Command\SubmitEnquiry;
use App\Model\Enquiry\EnquiryId;
use App\Model\Enquiry\Name;
use App\Util\Assert;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\MessageBusInterface;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Util\StringUtil;

class SendEnquiryMutation implements MutationInterface
{
    public function __construct(private MessageBusInterface $commandBus)
    {
    }

    public function __invoke(Argument $args): array
    {
        $enquiry = $args['enquiry'];

        $message = StringUtil::trim($enquiry['message']);
        Assert::lengthBetween(
            $message,
            10,
            10000,
            'Message must be between %2$s and %3$s characters. Got: "%s".',
        );

        $this->commandBus->dispatch(
            SubmitEnquiry::with(
                EnquiryId::fromUuid(Uuid::uuid4()),
                Name::fromString($enquiry['name']),
                Email::fromString($enquiry['email']),
                $message,
            ),
        );

        return [
            'success' => true,
        ];
    }
}
