<?php

declare(strict_types=1);

namespace App\Tests\Form;

use App\Form\User\UserProfileType;
use App\Model\Email;
use App\Model\User\Name;
use Faker;
use Symfony\Component\Form\Test\Traits\ValidatorExtensionTrait;
use Symfony\Component\Form\Test\TypeTestCase;

class UserProfileTypeTest extends TypeTestCase
{
    use ValidatorExtensionTrait;

    public function test()
    {
        $faker = Faker\Factory::create();

        $formData = [
            'email'     => $faker->email,
            'firstName' => $faker->name,
            'lastName'  => $faker->name,
        ];

        $form = $this->factory->create(UserProfileType::class)
            ->submit($formData);

        $this->assertTrue($form->isValid());

        $this->assertInstanceOf(Email::class, $form->getData()['email']);
        $this->assertInstanceOf(Name::class, $form->getData()['firstName']);
        $this->assertInstanceOf(Name::class, $form->getData()['lastName']);
    }
}
