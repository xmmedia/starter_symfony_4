<?php

declare(strict_types=1);

namespace App\DataFixtures\Faker\Provider;

use App\Model\Auth\AuthId;
use App\Model\Enquiry\EnquiryId;
use App\Model\User\UserId;
use Faker;

/**
 * @codeCoverageIgnore
 */
class UuidFakerProvider extends Faker\Provider\Base
{
    public function authId(): AuthId
    {
        return AuthId::fromString($this->generator->ext(Faker\Extension\UuidExtension::class)->uuid3());
    }

    public function enquiryId(): EnquiryId
    {
        return EnquiryId::fromString($this->generator->ext(Faker\Extension\UuidExtension::class)->uuid3());
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->generator->ext(Faker\Extension\UuidExtension::class)->uuid3());
    }
}
