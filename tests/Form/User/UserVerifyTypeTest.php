<?php

declare(strict_types=1);

namespace App\Tests\Form\User;

use App\Form\User\UserVerifyType;
use App\Model\User\Token;
use App\Tests\TypeTestCase;
use Faker;

class UserVerifyTypeTest extends TypeTestCase
{
    public function test()
    {
        $faker = Faker\Factory::create();

        $newPassword = $faker->password(12, 250);

        $formData = [
            'token'       => $faker->password(12, 250),
            'password'    => [
                'first'  => $newPassword,
                'second' => $newPassword,
            ],
        ];

        $form = $this->factory->create(UserVerifyType::class)
            ->submit($formData);

        $this->assertFormIsValid($form);
        $this->hasAllFormFields($form, $formData);

        $this->assertInstanceOf(Token::class, $form->getData()['token']);
    }
}
