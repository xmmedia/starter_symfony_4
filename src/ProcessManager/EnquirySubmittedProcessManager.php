<?php

declare(strict_types=1);

namespace App\ProcessManager;

use App\Model\Enquiry\Command\SendEnquiryEmail;
use App\Model\Enquiry\Event\EnquiryWasSubmitted;
use Symfony\Component\Messenger\MessageBusInterface;

class EnquirySubmittedProcessManager
{
    /** @var MessageBusInterface */
    private $commandBus;

    public function __construct(MessageBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function __invoke(EnquiryWasSubmitted $event): void
    {
        $this->commandBus->dispatch(SendEnquiryEmail::with(
            $event->name(),
            $event->email(),
            $event->message()
        ));
    }
}
