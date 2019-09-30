<?php

declare(strict_types=1);

namespace App\Tests;

use App\DataFixtures\Faker\Provider;
use Faker;
use Xm\SymfonyBundle\DataFixtures\Faker\Provider as BundleProviders;

trait UsesFaker
{
    /** @var Faker\Generator */
    private $faker;

    /**
     * @return Faker\Generator|BundleProviders\AddressFakerProvider|BundleProviders\DateFakerProvider|BundleProviders\EmailFakerProvider|BundleProviders\GenderFakerProvider|BundleProviders\InternetFakerProvider|BundleProviders\NameFakerProvider|BundleProviders\PhoneNumberFakerProvider|BundleProviders\StringFakerProvider|BundleProviders\UuidFakerProvider|Provider\UuidFakerProvider
     */
    protected function faker(): Faker\Generator
    {
        return null === $this->faker ? $this->makeFaker() : $this->faker;
    }

    private function makeFaker(): Faker\Generator
    {
        $locales = ['en_CA', 'en_US'];

        $this->faker = Faker\Factory::create($locales[array_rand($locales)]);
        $this->faker->addProvider(new BundleProviders\AddressFakerProvider($this->faker));
        $this->faker->addProvider(new BundleProviders\DateFakerProvider($this->faker));
        $this->faker->addProvider(new BundleProviders\EmailFakerProvider($this->faker));
        $this->faker->addProvider(new BundleProviders\GenderFakerProvider($this->faker));
        $this->faker->addProvider(new BundleProviders\InternetFakerProvider($this->faker));
        $this->faker->addProvider(new BundleProviders\NameFakerProvider($this->faker));
        $this->faker->addProvider(new BundleProviders\PhoneNumberFakerProvider($this->faker));
        $this->faker->addProvider(new BundleProviders\StringFakerProvider($this->faker));
        $this->faker->addProvider(new BundleProviders\UuidFakerProvider($this->faker));
        $this->faker->addProvider(new Provider\UuidFakerProvider($this->faker));

        return $this->faker;
    }
}
