<?php

declare(strict_types=1);

namespace App\Tests\Form\User;

use App\Form\User\UserRecoverResetType;
use App\Model\User\Token;
use Faker;
use Symfony\Component\Form\Test\Traits\ValidatorExtensionTrait;
use Symfony\Component\Form\Test\TypeTestCase;

class UserRecoverResetTypeTest extends TypeTestCase
{
    use ValidatorExtensionTrait;

    public function test()
    {
        $faker = Faker\Factory::create();

        $newPassword = $faker->password(12, 250);

        $formData = [
            'token'       => $faker->password(12, 250),
            'newPassword' => [
                'first'  => $newPassword,
                'second' => $newPassword,
            ],
        ];

        $form = $this->factory->create(UserRecoverResetType::class)
            ->submit($formData);

        $this->assertTrue($form->isValid());

        $this->assertInstanceOf(Token::class, $form->getData()['token']);
    }
}
