<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Model\Enquiry\Enquiry;
use App\Model\Enquiry\EnquiryId;
use App\Model\Enquiry\EnquiryList;
use Prooph\EventSourcing\Aggregate\AggregateRepository;

final class EnquiryRepository extends AggregateRepository implements EnquiryList
{
    public function save(Enquiry $enquiry): void
    {
        $this->saveAggregateRoot($enquiry);
    }

    public function get(EnquiryId $enquiryId): ?Enquiry
    {
        return $this->getAggregateRoot($enquiryId->toString());
    }
}
