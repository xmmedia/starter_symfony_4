<?php

declare(strict_types=1);

namespace App\Tests\Form;

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

        $formData = [
            'currentPassword' => $faker->password,
            'newPassword'     => [
                'first'  => $faker->password,
                'second' => $faker->password,
            ],
        ];

        $form = $this->factory->create(UserChangePasswordType::class)
            ->submit($formData);

        $this->assertTrue($form->isValid());
    }
}
