<?php

namespace App\DataFixtures\Faker\Provider;

use Faker\Provider\en_CA\PhoneNumber;
use libphonenumber\PhoneNumberUtil;

class PhoneNumberFakerProvider extends PhoneNumber
{
    public function formatPhoneNumber()
    {
        $util = PhoneNumberUtil::getInstance();

        return $util->parse(static::phoneNumber(), 'CA');
    }
}
