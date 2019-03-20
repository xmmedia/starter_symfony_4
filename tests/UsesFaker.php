<?php

declare(strict_types=1);

namespace App\Tests;

use App\DataFixtures\Faker\Provider;
use Faker;

trait UsesFaker
{
    /** @var Faker\Generator */
    private $faker;

    /**
     * @return Faker\Generator|Provider\AddressFakerProvider|Provider\EmailFakerProvider|Provider\InternetFakerProvider|Provider\NameFakerProvider|Provider\PhoneNumberFakerProvider|Provider\StringFakerProvider|Provider\UuidFakerProvider
     */
    protected function faker(): Faker\Generator
    {
        return is_null($this->faker) ? $this->makeFaker() : $this->faker;
    }

    private function makeFaker(): Faker\Generator
    {
        $locales = ['en_CA', 'en_US'];

        $this->faker = Faker\Factory::create($locales[array_rand($locales)]);
        $this->faker->addProvider(new Provider\AddressFakerProvider($this->faker));
        $this->faker->addProvider(new Provider\EmailFakerProvider($this->faker));
        $this->faker->addProvider(new Provider\InternetFakerProvider($this->faker));
        $this->faker->addProvider(new Provider\NameFakerProvider($this->faker));
        $this->faker->addProvider(new Provider\PhoneNumberFakerProvider($this->faker));
        $this->faker->addProvider(new Provider\StringFakerProvider($this->faker));
        $this->faker->addProvider(new Provider\UuidFakerProvider($this->faker));

        return $this->faker;
    }
}
