<?php

declare(strict_types=1);

namespace App\DataFixtures\Faker\Provider;

use App\Model\Enquiry\EnquiryId;

/**
 * @property EnquiryId $enquiryId
 *
 * @codeCoverageIgnore
 */
class UuidFakerProvider extends \Xm\SymfonyBundle\DataFixtures\Faker\Provider\UuidFakerProvider
{
    public function enquiryId(): EnquiryId
    {
        return EnquiryId::fromString(parent::uuid());
    }
}
