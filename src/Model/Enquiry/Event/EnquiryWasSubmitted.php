<?php

declare(strict_types=1);

namespace App\Model\Enquiry\Event;

use App\Model\Email;
use App\Model\Enquiry\EnquiryId;
use Prooph\EventSourcing\AggregateChanged;

class EnquiryWasSubmitted extends AggregateChanged
{
    /** @var string */
    private $name;

    /** @var Email */
    private $email;

    /** @var string */
    private $message;

    public static function now(
        EnquiryId $enquiryId,
        string $name,
        Email $email,
        string $message
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
        if (null === $this->name) {
            $this->name = $this->payload()['name'];
        }

        return $this->name;
    }

    public function email(): Email
    {
        if (null === $this->email) {
            $this->email = Email::fromString($this->payload()['email']);
        }

        return $this->email;
    }

    public function message(): string
    {
        if (null === $this->message) {
            $this->message = $this->payload()['message'];
        }

        return $this->message;
    }
}
