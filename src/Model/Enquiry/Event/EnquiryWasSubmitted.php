<?php

declare(strict_types=1);

namespace App\Model\Enquiry\Event;

use App\Model\Enquiry\EnquiryId;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;
use Xm\SymfonyBundle\Model\Email;

class EnquiryWasSubmitted extends AggregateChanged
{
    private readonly string $name;
    private readonly Email $email;
    private readonly string $message;

    public static function now(
        EnquiryId $enquiryId,
        string $name,
        Email $email,
        string $message,
    ): self {
        $event = self::occur($enquiryId->toString(), [
            'name'    => $name,
            'email'   => $email->toString(),
            'message' => $message,
        ]);

        $event->name = $name;
        $event->email = $email;
        $event->message = $message;

        return $event;
    }

    public function enquiryId(): EnquiryId
    {
        return EnquiryId::fromString($this->aggregateId());
    }

    public function name(): string
    {
        if (!isset($this->name)) {
            $this->name = $this->payload['name'];
        }

        return $this->name;
    }

    public function email(): Email
    {
        if (!isset($this->email)) {
            $this->email = Email::fromString($this->payload['email']);
        }

        return $this->email;
    }

    public function message(): string
    {
        if (!isset($this->message)) {
            $this->message = $this->payload['message'];
        }

        return $this->message;
    }
}
