<?php

declare(strict_types=1);

namespace App\Model\Enquiry;

use Xm\SymfonyBundle\Model\UuidId;
use Xm\SymfonyBundle\Model\UuidInterface;
use Xm\SymfonyBundle\Model\ValueObject;

class EnquiryId implements ValueObject, UuidInterface
{
    use UuidId;
}
