<?php

declare(strict_types=1);

namespace App\Model\Enquiry;

use App\Model\UuidId;
use App\Model\UuidInterface;
use App\Model\ValueObject;

class EnquiryId implements ValueObject, UuidInterface
{
    use UuidId;
}
