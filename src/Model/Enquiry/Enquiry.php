<?php

declare(strict_types=1);

namespace App\Model\Enquiry;

use App\Util\Assert;
use Xm\SymfonyBundle\EventSourcing\Aggregate\AggregateRoot;
use Xm\SymfonyBundle\EventSourcing\AppliesAggregateChanged;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Model\Entity;

class Enquiry extends AggregateRoot implements Entity
{
    use AppliesAggregateChanged;

    public const MESSAGE_MIN_LENGTH = 10;
    public const MESSAGE_MAX_LENGTH = 5000;

    private EnquiryId $enquiryId;

    public static function submit(
        EnquiryId $enquiryId,
        string $name,
        Email $email,
        string $message,
    ): self {
        Assert::minLength($message, self::MESSAGE_MIN_LENGTH);
        Assert::maxLength($message, self::MESSAGE_MAX_LENGTH);

        $self = new self();
        $self->recordThat(
            Event\EnquiryWasSubmitted::now($enquiryId, $name, $email, $message),
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
        if (static::class !== \get_class($other)) {
            return false;
        }

        return $this->enquiryId->sameValueAs($other->enquiryId);
    }
}
