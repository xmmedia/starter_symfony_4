<?php

declare(strict_types=1);

namespace App\Model\Enquiry;

use App\EventSourcing\Aggregate\AggregateRoot;
use App\EventSourcing\AppliesAggregateChanged;
use App\Model\Email;
use App\Model\Entity;

class Enquiry extends AggregateRoot implements Entity
{
    use AppliesAggregateChanged;

    public const NAME_MIN_LENGTH = 3;
    public const NAME_MAX_LENGTH = 50;
    public const MESSAGE_MIN_LENGTH = 10;
    public const MESSAGE_MAX_LENGTH = 5000;

    /** @var EnquiryId */
    private $enquiryId;

    public static function submit(
        EnquiryId $enquiryId,
        string $name,
        Email $email,
        string $message
    ): self {
        $self = new self();
        $self->recordThat(
            Event\EnquiryWasSubmitted::now($enquiryId, $name, $email, $message)
        );

        return $self;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function aggregateId(): string
    {
        return $this->enquiryId->toString();
    }

    public function enquiryId(): EnquiryId
    {
        return $this->enquiryId;
    }

    protected function whenEnquiryWasSubmitted(Event\EnquiryWasSubmitted $event): void
    {
        $this->enquiryId = $event->enquiryId();
    }

    /**
     * @param Enquiry|Entity $other
     */
    public function sameIdentityAs(Entity $other): bool
    {
        if (get_class($this) !== get_class($other)) {
            return false;
        }

        return $this->enquiryId->sameValueAs($other->enquiryId);
    }
}
