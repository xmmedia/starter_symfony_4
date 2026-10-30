<?php

declare(strict_types=1);

namespace App\DataFixtures\Faker;

/**
 * The methods the custom providers add to Faker\Generator via __call().
 *
 * Never implemented — only intersected with Faker\Generator (see UsesFaker), since
 * intersecting the provider classes themselves is an impossible type. Keep in sync
 * with UsesFaker::makeFaker(); bundle/app tell the two UuidFakerProviders apart.
 *
 * Omits password() & name() (Faker\Generator declares both) and fakeId() (test-only
 * return type). Chaining off unique() resolves these too, see below.
 *
 * @method array<string, string|null>                    addressArray()                                                bundle AddressFakerProvider
 * @method \Xm\SymfonyBundle\Model\Address               addressVo()                                                   bundle AddressFakerProvider
 * @method \Xm\SymfonyBundle\Model\Country               countryVo()                                                   bundle AddressFakerProvider
 * @method \Xm\SymfonyBundle\Model\Province              provinceVo()                                                  bundle AddressFakerProvider
 * @method \Xm\SymfonyBundle\Model\Date                  dateVoBetween(string $min = '-30 years', string $max = 'now') bundle DateFakerProvider
 * @method \Xm\SymfonyBundle\Model\Email                 emailVo()                                                     bundle EmailFakerProvider
 * @method string                                        gender()                                                      bundle GenderFakerProvider
 * @method \Xm\SymfonyBundle\Model\PhoneNumber           phoneNumberVo()                                               bundle PhoneNumberFakerProvider
 * @method string                                        string(int $length)                                           bundle StringFakerProvider
 * @method \Xm\SymfonyBundle\Model\EmailGatewayMessageId emailGatewayMessageId()                                       bundle UuidFakerProvider
 * @method \App\Model\User\Role                          userRole()                                                    app Provider\UserFakerProvider
 * @method \App\Model\User\UserData                      userData()                                                    app Provider\UserFakerProvider
 * @method \App\Model\Auth\AuthId                        authId()                                                      app Provider\UuidFakerProvider
 * @method \App\Model\User\UserId                        userId()                                                      app Provider\UuidFakerProvider
 */
interface FakerGenerator
{
    /**
     * Faker\Generator declares @return self, which drops this interface from the
     * intersection; static keeps it, so unique() chains resolve the methods above.
     */
    public function unique(bool $reset = false, int $maxRetries = 10000): static;
}
