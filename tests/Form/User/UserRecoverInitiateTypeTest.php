<?php

declare(strict_types=1);

namespace App\Tests\Form\User;

use App\Form\User\UserRecoverInitiateType;
use App\Model\Email;
use App\Tests\TypeTestCase;
use Faker;

class UserRecoverInitiateTypeTest extends TypeTestCase
{
    public function test()
    {
        $faker = Faker\Factory::create();

        $formData = [
            'email' => $faker->email,
        ];

        $form = $this->factory->create(UserRecoverInitiateType::class)
            ->submit($formData);

        $this->assertFormIsValid($form);
        $this->hasAllFormFields($form, $formData);

        $this->assertInstanceOf(Email::class, $form->getData()['email']);
    }
}
