<?php

declare(strict_types=1);

namespace App\Tests;

use App\DataFixtures\Faker\FakerGenerator;
use App\DataFixtures\Faker\Provider;
use Faker;
use Xm\SymfonyBundle\DataFixtures\Faker\Provider as BundleProviders;

trait UsesFaker
{
    /** @var Faker\Generator&FakerGenerator */
    private static Faker\Generator $faker;

    /**
     * Static so that it can also be used from data providers,
     * which must be static as of PHPUnit 10.
     *
     * @return Faker\Generator&BundleProviders\AddressFakerProvider&BundleProviders\DateFakerProvider&BundleProviders\EmailFakerProvider&BundleProviders\GenderFakerProvider&BundleProviders\InternetFakerProvider&BundleProviders\NameFakerProvider&BundleProviders\PhoneNumberFakerProvider&BundleProviders\StringFakerProvider&BundleProviders\UuidFakerProvider&Provider\UserFakerProvider&Provider\UuidFakerProvider
     *
     * @phpstan-return Faker\Generator&FakerGenerator
     */
    protected static function faker(): Faker\Generator
    {
        if (!isset(self::$faker)) {
            self::$faker = self::makeFaker();
        }

        return self::$faker;
    }

    /**
     * @return Faker\Generator&BundleProviders\AddressFakerProvider&BundleProviders\DateFakerProvider&BundleProviders\EmailFakerProvider&BundleProviders\GenderFakerProvider&BundleProviders\InternetFakerProvider&BundleProviders\NameFakerProvider&BundleProviders\PhoneNumberFakerProvider&BundleProviders\StringFakerProvider&BundleProviders\UuidFakerProvider&Provider\UserFakerProvider&Provider\UuidFakerProvider
     *
     * @phpstan-return Faker\Generator&FakerGenerator
     */
    protected static function makeFaker(): Faker\Generator
    {
        $locales = ['en_CA', 'en_US'];

        /** @var Faker\Generator&FakerGenerator $faker */
        $faker = Faker\Factory::create($locales[array_rand($locales)]);
        $faker->addProvider(new BundleProviders\AddressFakerProvider($faker));
        $faker->addProvider(new BundleProviders\DateFakerProvider($faker));
        $faker->addProvider(new BundleProviders\EmailFakerProvider($faker));
        $faker->addProvider(new BundleProviders\GenderFakerProvider($faker));
        $faker->addProvider(new BundleProviders\InternetFakerProvider($faker));
        $faker->addProvider(new BundleProviders\NameFakerProvider($faker));
        $faker->addProvider(new BundleProviders\PhoneNumberFakerProvider($faker));
        $faker->addProvider(new BundleProviders\StringFakerProvider($faker));
        $faker->addProvider(new BundleProviders\UuidFakerProvider($faker));
        $faker->addProvider(new Provider\UserFakerProvider($faker));
        $faker->addProvider(new Provider\UuidFakerProvider($faker));

        return $faker;
    }
}
