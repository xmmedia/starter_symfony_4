<?php

declare(strict_types=1);

namespace App\Tests\Form\User;

use App\Form\User\UserChangePasswordType;
use Faker;
use Symfony\Component\Form\Test\Traits\ValidatorExtensionTrait;
use Symfony\Component\Form\Test\TypeTestCase;

class UserChangePasswordTypeTest extends TypeTestCase
{
    use ValidatorExtensionTrait;

    public function test()
    {
        $faker = Faker\Factory::create();

        $newPassword = $faker->password(12, 250);

        $formData = [
            'currentPassword' => $faker->password(12, 250),
            'newPassword'     => [
                'first'  => $newPassword,
                'second' => $newPassword,
            ],
        ];

        $form = $this->factory->create(UserChangePasswordType::class)
            ->submit($formData);

        $this->assertTrue($form->isValid());
    }
}
