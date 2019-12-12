<?php

declare(strict_types=1);

namespace App\DataFixtures\Faker\Provider;

use App\Model\Auth\AuthId;
use App\Model\Enquiry\EnquiryId;
use App\Model\User\UserId;
use Faker;

/**
 * @property EnquiryId $enquiryId
 *
 * @codeCoverageIgnore
 */
class UuidFakerProvider extends Faker\Provider\Uuid
{
    public function authId(): AuthId
    {
        return AuthId::fromString(parent::uuid());
    }

    public function enquiryId(): EnquiryId
    {
        return EnquiryId::fromString(parent::uuid());
    }

    public function userId(): UserId
    {
        return UserId::fromString(parent::uuid());
    }
}
