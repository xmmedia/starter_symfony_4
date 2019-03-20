<?php

declare(strict_types=1);

namespace App\Tests\Model;

trait PhoneNumberDataProvider
{
    public function phoneNumberValidProvider(): \Generator
    {
        yield [
            '403-543-3233',
            ['phoneNumber' => '+14035433233', 'extension' => null],
        ];

        yield [
            '1-403-543-3233',
            ['phoneNumber' => '+14035433233', 'extension' => null],
        ];

        yield [
            '403.543.3233',
            ['phoneNumber' => '+14035433233', 'extension' => null],
        ];

        yield [
            '1.403.543.3233',
            ['phoneNumber' => '+14035433233', 'extension' => null],
        ];

        yield [
            '+1-403-543-3233',
            ['phoneNumber' => '+14035433233', 'extension' => null],
        ];

        yield [
            '+1-403-543-3233 x 3233',
            ['phoneNumber' => '+14035433233', 'extension' => '3233'],
        ];

        yield [
            '+1-403-543-3233 ext 3233',
            ['phoneNumber' => '+14035433233', 'extension' => '3233'],
        ];

        yield [
            '+1-403-543-3233; 3233',
            ['phoneNumber' => '+14035433233', 'extension' => '3233'],
        ];

        yield [
            '+1-403-543-3233 extn 3233',
            ['phoneNumber' => '+14035433233', 'extension' => '3233'],
        ];

        yield [
            '+1-403-543-3233 extension 3233',
            ['phoneNumber' => '+14035433233', 'extension' => '3233'],
        ];

        yield [
            '+1-403-543-3233 x3233',
            ['phoneNumber' => '+14035433233', 'extension' => '3233'],
        ];

        yield [
            '(403) 543-3233',
            ['phoneNumber' => '+14035433233', 'extension' => null],
        ];

        yield [
            '(403)543-3233',
            ['phoneNumber' => '+14035433233', 'extension' => null],
        ];

        yield [
            '(403)543-3233',
            ['phoneNumber' => '+14035433233', 'extension' => null],
        ];

        yield [
            '(403)543-3233 ext 123',
            ['phoneNumber' => '+14035433233', 'extension' => '123'],
        ];

        yield [
            '(403)543-3233 x 123',
            ['phoneNumber' => '+14035433233', 'extension' => '123'],
        ];

        yield [
            '1 (403)543-3233 x 123',
            ['phoneNumber' => '+14035433233', 'extension' => '123'],
        ];

        yield [
            '+1 (403)543-3233 x 123',
            ['phoneNumber' => '+14035433233', 'extension' => '123'],
        ];

        yield [
            '201-886-0269 x3767',
            ['phoneNumber' => '+12018860269', 'extension' => '3767'],
        ];

        yield [
            '(888) 937-7238',
            ['phoneNumber' => '+18889377238', 'extension' => null],
        ];

        yield [
            '+27113456789',
            ['phoneNumber' => '+27113456789', 'extension' => null],
        ];

        yield [
            '+17093456789',
            ['phoneNumber' => '+17093456789', 'extension' => null],
        ];

        yield [
            // With letters
            '403-323-ABCD',
            ['phoneNumber' => '+14033232223', 'extension' => null],
        ];

        yield [
            // Mexico
            '+52-777-543-3233',
            ['phoneNumber' => '+527775433233', 'extension' => null],
        ];
    }

    public function phoneNumberInvalidProvider(): \Generator
    {
        // Mexico, but no country code
        yield ['711-543-3233'];
        // too many numbers
        yield ['403-323-32333'];
        // too short
        yield ['403'];
        // no area code
        yield ['423-4344'];
        // no extension prefix
        yield ['403-323-3233 3423'];
        // no extension prefix
        yield ['+011+777-323-3233'];
        // not phone numbers
        yield ['string'];
        yield ['ABC'];
    }
}
