<?php

declare(strict_types=1);

namespace App\DataFixtures\Faker\Provider;

use App\Model\PhoneNumber;
use Faker\Provider\en_CA\PhoneNumber as FakerPhoneNumber;
use libphonenumber\PhoneNumberUtil;

/**
 * @codeCoverageIgnore
 */
class PhoneNumberFakerProvider extends FakerPhoneNumber
{
    public function phoneNumberVo()
    {
        $util = PhoneNumberUtil::getInstance();
        $phoneNumber = $util->parse(static::phoneNumber(), 'CA');

        return PhoneNumber::fromObject($phoneNumber);
    }
}
