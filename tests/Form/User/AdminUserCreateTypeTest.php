<?php

declare(strict_types=1);

namespace App\Tests\Form\User;

use App\DataProvider\RoleProvider;
use App\Form\User\AdminUserCreateType;
use App\Form\DataTransformer\SecurityRoleTransformer;
use App\Model\Email;
use App\Model\User\Name;
use App\Model\User\Service\ChecksUniqueUsersEmail;
use App\Tests\TypeTestCase;
use App\Validator\Constraints\UniqueNewUserEmailValidator;
use Faker;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class AdminUserCreateTypeTest extends TypeTestCase
{
    use MockeryPHPUnitIntegration;

    public function setUp(): void
    {
        parent::setUp();

        $checker = Mockery::mock(ChecksUniqueUsersEmail::class);
        $checker->shouldReceive('__invoke')
            ->andReturnNull();

        $this->validatorContainer->set(
            UniqueNewUserEmailValidator::class,
            new UniqueNewUserEmailValidator($checker)
        );
    }

    protected function getTypes(): array
    {
        $extensions = parent::getTypes();

        $roleHierarchy = Mockery::mock(RoleHierarchyInterface::class);
        $roleHierarchy->shouldReceive('getReachableRoles')
            ->andReturn([new Role('ROLE_USER')]);

        $roleTransformer = Mockery::mock(SecurityRoleTransformer::class);
        $roleTransformer->shouldReceive('transform')
            ->with(null)
            ->andReturnNull();
        $roleTransformer->shouldReceive('reverseTransform')
            ->with('ROLE_USER')
            ->andReturn(new Role('ROLE_USER'));

        $extensions[] = new AdminUserCreateType(
            new RoleProvider($roleHierarchy),
            $roleTransformer
        );

        return $extensions;
    }

    public function test()
    {
        $faker = Faker\Factory::create();

        $formData = [
            'email'       => $faker->email,
            'setPassword' => true,
            'password'    => $faker->password(12, 250),
            'firstName'   => $faker->name,
            'lastName'    => $faker->name,
            'role'        => 'ROLE_USER',
            'active'      => true,
            'sendInvite'  => true,
        ];

        $form = $this->factory->create(AdminUserCreateType::class)
            ->submit($formData);

        $this->assertFormIsValid($form);
        $this->hasAllFormFields($form, $formData);

        $this->assertInstanceOf(Email::class, $form->getData()['email']);
        $this->assertInstanceOf(Role::class, $form->getData()['role']);
        $this->assertInstanceOf(Name::class, $form->getData()['firstName']);
        $this->assertInstanceOf(Name::class, $form->getData()['lastName']);
    }

    public function testNoPassword()
    {
        $faker = Faker\Factory::create();

        $formData = [
            'email'       => $faker->email,
            'setPassword' => false,
            'firstName'   => $faker->name,
            'lastName'    => $faker->name,
            'role'        => 'ROLE_USER',
            'active'      => true,
            'sendInvite'  => true,
        ];

        $form = $this->factory->create(AdminUserCreateType::class)
            ->submit($formData);

        $this->assertFormIsValid($form);
        $this->hasAllFormFields($form, $formData);
    }
}
