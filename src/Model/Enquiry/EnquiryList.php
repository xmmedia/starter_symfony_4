<?php

declare(strict_types=1);

namespace App\Model\Enquiry;

interface EnquiryList
{
    public function save(Enquiry $enquiry): void;

    public function get(EnquiryId $enquiryId): ?Enquiry;
}
