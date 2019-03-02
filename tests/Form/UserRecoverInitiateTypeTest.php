<?php

declare(strict_types=1);

namespace App\Tests\Form;

use App\Form\User\UserRecoverInitiateType;
use App\Model\Email;
use Faker;
use Symfony\Component\Form\Test\Traits\ValidatorExtensionTrait;
use Symfony\Component\Form\Test\TypeTestCase;

class UserRecoverInitiateTypeTest extends TypeTestCase
{
    use ValidatorExtensionTrait;

    public function test()
    {
        $faker = Faker\Factory::create();

        $formData = [
            'email' => $faker->email,
        ];

        $form = $this->factory->create(UserRecoverInitiateType::class)
            ->submit($formData);

        $this->assertTrue($form->isValid());

        $this->assertInstanceOf(Email::class, $form->getData()['email']);
    }
}
