<?php

declare(strict_types=1);

namespace App\DataFixtures\Faker;

use App\Model\Auth\AuthId;
use App\Model\User\Role;
use App\Model\User\UserData;
use App\Model\User\UserId;
use Xm\SymfonyBundle\Model\Address;
use Xm\SymfonyBundle\Model\Country;
use Xm\SymfonyBundle\Model\Date;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Model\EmailGatewayMessageId;
use Xm\SymfonyBundle\Model\PhoneNumber;
use Xm\SymfonyBundle\Model\Province;

/**
 * The methods the custom providers add to Faker\Generator via __call().
 *
 * Never implemented — only intersected with Faker\Generator (see UsesFaker), since
 * intersecting the provider classes themselves is an impossible type. Keep in sync
 * with UsesFaker::makeFaker(); bundle/app tell the two UuidFakerProviders apart.
 *
 * Omits password() & name() (Faker\Generator declares both), fakeId() (test-only
 * return type) and anything chained off unique(), which returns a different class.
 *
 * @method array<string, string|null> addressArray()                                                bundle AddressFakerProvider
 * @method Address                    addressVo()                                                   bundle AddressFakerProvider
 * @method Country                    countryVo()                                                   bundle AddressFakerProvider
 * @method Province                   provinceVo()                                                  bundle AddressFakerProvider
 * @method Date                       dateVoBetween(string $min = '-30 years', string $max = 'now') bundle DateFakerProvider
 * @method Email                      emailVo()                                                     bundle EmailFakerProvider
 * @method string                     gender()                                                      bundle GenderFakerProvider
 * @method PhoneNumber                phoneNumberVo()                                               bundle PhoneNumberFakerProvider
 * @method string                     string(int $length)                                           bundle StringFakerProvider
 * @method EmailGatewayMessageId      emailGatewayMessageId()                                       bundle UuidFakerProvider
 * @method Role                       userRole()                                                    app Provider\UserFakerProvider
 * @method UserData                   userData()                                                    app Provider\UserFakerProvider
 * @method AuthId                     authId()                                                      app Provider\UuidFakerProvider
 * @method UserId                     userId()                                                      app Provider\UuidFakerProvider
 */
interface FakerGenerator
{
}
