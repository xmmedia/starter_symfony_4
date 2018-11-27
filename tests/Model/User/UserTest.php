<?php

declare(strict_types=1);

namespace App\Tests\Model\User;

use App\Model\Email;
use App\Model\EmailGatewayMessageId;
use App\Model\User\Name;
use App\Model\User\Token;
use App\Model\User\User;
use App\Model\User\UserId;
use App\Model\User\Event;
use App\Model\User\Exception;
use App\Tests\BaseTestCase;
use Faker;
use Symfony\Component\Security\Core\Role\Role;

class UserTest extends BaseTestCase
{
    public function testCreateByAdmin(): void
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $email = Email::fromString($faker->email);
        $password = $faker->password;
        $role = new Role('ROLE_USER');
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $user = User::createByAdmin(
            $userId,
            $email,
            $password,
            $role,
            true,
            $firstName,
            $lastName,
            false
        );

        $this->assertInstanceOf(User::class, $user);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserWasCreatedByAdmin::class,
            [
                'email'           => $email->toString(),
                'encodedPassword' => $password,
                'role'            => $role->getRole(),
                'active'          => true,
                'firstName'       => $firstName->toString(),
                'lastName'        => $lastName->toString(),
                'sendInvite'      => false,
            ],
            $events
        );

        $this->assertCount(1, $events);

        $this->assertEquals($userId, $user->userId());
        $this->assertTrue($user->verified());
        $this->assertTrue($user->active());
    }

    public function testCreateByAdminSendInvite(): void
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $email = Email::fromString($faker->email);
        $password = $faker->password;
        $role = new Role('ROLE_USER');
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $user = User::createByAdmin(
            $userId,
            $email,
            $password,
            $role,
            true,
            $firstName,
            $lastName,
            true
        );

        $this->assertInstanceOf(User::class, $user);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserWasCreatedByAdmin::class,
            [
                'email'           => $email->toString(),
                'encodedPassword' => $password,
                'role'            => $role->getRole(),
                'active'          => true,
                'firstName'       => $firstName->toString(),
                'lastName'        => $lastName->toString(),
                'sendInvite'      => true,
            ],
            $events
        );

        $this->assertCount(1, $events);

        $this->assertEquals($userId, $user->userId());
        $this->assertFalse($user->verified());
        $this->assertTrue($user->active());
    }

    public function testCreateByAdminNotActive(): void
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $email = Email::fromString($faker->email);
        $password = $faker->password;
        $role = new Role('ROLE_USER');
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $user = User::createByAdmin(
            $userId,
            $email,
            $password,
            $role,
            false,
            $firstName,
            $lastName,
            true
        );

        $this->assertInstanceOf(User::class, $user);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserWasCreatedByAdmin::class,
            [
                'email'           => $email->toString(),
                'encodedPassword' => $password,
                'role'            => $role->getRole(),
                'active'          => false,
                'firstName'       => $firstName->toString(),
                'lastName'        => $lastName->toString(),
                'sendInvite'      => false,
            ],
            $events
        );

        $this->assertCount(1, $events);

        $this->assertEquals($userId, $user->userId());
        $this->assertTrue($user->verified());
        $this->assertFalse($user->active());
    }

    public function testCreateByAdminMinimal(): void
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $email = Email::fromString($faker->email);
        $password = $faker->password;
        $role = new Role('ROLE_USER');

        $user = User::createByAdminMinimum($userId, $email, $password, $role);

        $this->assertInstanceOf(User::class, $user);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\MinimalUserWasCreatedByAdmin::class,
            [
                'email'           => $email->toString(),
                'encodedPassword' => $password,
                'role'            => $role->getRole(),
            ],
            $events
        );

        $this->assertCount(1, $events);

        $this->assertEquals($userId, $user->userId());
        $this->assertTrue($user->verified());
        $this->assertTrue($user->active());
    }

    public function testUpdateByAdmin(): void
    {
        $faker = Faker\Factory::create();

        $user = $this->getUserActive();

        $email = Email::fromString($faker->email);
        $role = new Role('ROLE_USER');
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $user->updateByAdmin($email, $role, $firstName, $lastName);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\AdminUpdatedUser::class,
            [
                'email'     => $email->toString(),
                'role'      => $role->getRole(),
                'firstName' => $firstName->toString(),
                'lastName'  => $lastName->toString(),
            ],
            $events
        );

        $this->assertCount(2, $events);
    }

    public function testChangePasswordByAdmin(): void
    {
        $faker = Faker\Factory::create();

        $user = $this->getUserActive();

        $password = $faker->password;

        $user->changePasswordByAdmin($password);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\AdminChangedPassword::class,
            [
                'encodedPassword' => $password,
            ],
            $events
        );

        $this->assertCount(2, $events);
    }

    public function testVerifyByAdmin(): void
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $email = Email::fromString($faker->email);
        $password = $faker->password;
        $role = new Role('ROLE_USER');
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $user = User::createByAdmin(
            $userId,
            $email,
            $password,
            $role,
            true,
            $firstName,
            $lastName,
            true // will set the user to unverified
        );

        $user->verifyByAdmin();

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserVerifiedByAdmin::class,
            [],
            $events
        );

        $this->assertCount(2, $events);

        $this->assertTrue($user->verified());
    }

    public function testVerifyByAdminAlreadyVerified(): void
    {
        $user = $this->getUserActive();

        $this->expectException(Exception\UserAlreadyVerified::class);

        $user->verifyByAdmin();
    }

    public function testActivateByAdmin(): void
    {
        $user = $this->getUserInactive();

        $user->activateByAdmin();

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserActivatedByAdmin::class,
            [],
            $events
        );

        $this->assertCount(2, $events);

        $this->assertTrue($user->active());
    }

    public function testActivateByAdminAlreadyActive(): void
    {
        $user = $this->getUserActive();

        $this->expectException(Exception\InvalidUserActiveStatus::class);

        $user->activateByAdmin();
    }

    public function testDeactivateByAdmin(): void
    {
        $user = $this->getUserActive();

        $user->deactivateByAdmin();

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserDeactivatedByAdmin::class,
            [],
            $events
        );

        $this->assertCount(2, $events);

        $this->assertFalse($user->active());
    }

    public function testDeactivateByAdminAlreadyInactive(): void
    {
        $user = $this->getUserInactive();

        $this->expectException(Exception\InvalidUserActiveStatus::class);

        $user->deactivateByAdmin();
    }

    public function testInviteSent(): void
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $email = Email::fromString($faker->email);
        $password = $faker->password;
        $role = new Role('ROLE_USER');
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $user = User::createByAdmin(
            $userId,
            $email,
            $password,
            $role,
            true,
            $firstName,
            $lastName,
            true
        );

        $token = Token::fromString($faker->asciify(str_repeat('*', 25)));
        $messageId = EmailGatewayMessageId::fromString($faker->uuid);

        $user->inviteSent($token, $messageId);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\InviteSent::class,
            [
                'token'     => $token->toString(),
                'messageId' => $messageId->toString(),
            ],
            $events
        );

        $this->assertCount(2, $events);
    }

    public function testInviteSentAlreadyVerified(): void
    {
        $faker = Faker\Factory::create();
        $token = Token::fromString($faker->asciify(str_repeat('*', 25)));
        $messageId = EmailGatewayMessageId::fromString($faker->uuid);

        $user = $this->getUserActive();

        $this->expectException(Exception\UserAlreadyVerified::class);

        $user->inviteSent($token, $messageId);
    }

    public function testVerify(): void
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $email = Email::fromString($faker->email);
        $password = $faker->password;
        $role = new Role('ROLE_USER');
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $user = User::createByAdmin(
            $userId,
            $email,
            $password,
            $role,
            true,
            $firstName,
            $lastName,
            true
        );

        $user->verify();

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(Event\UserVerified::class, [], $events);

        $this->assertCount(2, $events);

        $this->assertTrue($user->verified());
    }

    public function testVerifyAlreadyVerified(): void
    {
        $user = $this->getUserActive();

        $this->expectException(Exception\UserAlreadyVerified::class);

        $user->verify();
    }

    public function testVerifyInactive(): void
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $email = Email::fromString($faker->email);
        $password = $faker->password;
        $role = new Role('ROLE_USER');
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $user = User::createByAdmin(
            $userId,
            $email,
            $password,
            $role,
            true,
            $firstName,
            $lastName,
            true
        );

        $user->deactivateByAdmin();

        $this->expectException(Exception\InvalidUserActiveStatus::class);

        $user->verify();
    }

    public function testPasswordRecoverySent(): void
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $email = Email::fromString($faker->email);
        $password = $faker->password;
        $role = new Role('ROLE_USER');
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $user = User::createByAdmin(
            $userId,
            $email,
            $password,
            $role,
            true,
            $firstName,
            $lastName,
            true
        );

        $token = Token::fromString($faker->asciify(str_repeat('*', 25)));
        $messageId = EmailGatewayMessageId::fromString($faker->uuid);

        $user->passwordRecoverySent($token, $messageId);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\PasswordRecoverySent::class,
            [
                'token'     => $token->toString(),
                'messageId' => $messageId->toString(),
            ],
            $events
        );

        $this->assertCount(2, $events);
    }

    public function testPasswordRecoverySentInactive(): void
    {
        $faker = Faker\Factory::create();

        $user = $this->getUserInactive();

        $token = Token::fromString($faker->asciify(str_repeat('*', 25)));
        $messageId = EmailGatewayMessageId::fromString($faker->uuid);

        $this->expectException(Exception\InvalidUserActiveStatus::class);

        $user->passwordRecoverySent($token, $messageId);
    }

    public function testUpdate(): void
    {
        $faker = Faker\Factory::create();

        $user = $this->getUserActive();

        $email = Email::fromString($faker->email);
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $user->update($email, $firstName, $lastName);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserUpdatedProfile::class,
            [
                'email'     => $email->toString(),
                'firstName' => $firstName->toString(),
                'lastName'  => $lastName->toString(),
            ],
            $events
        );

        $this->assertCount(2, $events);
    }

    public function testUpdateInactive(): void
    {
        $faker = Faker\Factory::create();

        $user = $this->getUserInactive();

        $email = Email::fromString($faker->email);
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $this->expectException(Exception\InvalidUserActiveStatus::class);

        $user->update($email, $firstName, $lastName);
    }

    public function testLoggedIn(): void
    {
        $user = $this->getUserActive();

        $user->loggedIn();

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserLoggedIn::class,
            [],
            $events
        );

        $this->assertCount(2, $events);
    }

    public function testLoggedInUnverified(): void
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $email = Email::fromString($faker->email);
        $password = $faker->password;
        $role = new Role('ROLE_USER');
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $user = User::createByAdmin(
            $userId,
            $email,
            $password,
            $role,
            true,
            $firstName,
            $lastName,
            true
        );

        $this->expectException(Exception\UserNotVerified::class);

        $user->loggedIn();
    }

    public function testLoggedInInactive(): void
    {
        $user = $this->getUserInactive();

        $this->expectException(Exception\InvalidUserActiveStatus::class);

        $user->loggedIn();
    }

    public function testChangePassword(): void
    {
        $faker = Faker\Factory::create();

        $password = $faker->password;

        $user = $this->getUserActive();

        $user->changePassword($password);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\ChangedPassword::class,
            ['encodedPassword' => $password],
            $events
        );

        $this->assertCount(2, $events);
    }

    public function testChangePasswordInactive(): void
    {
        $faker = Faker\Factory::create();

        $password = $faker->password;

        $user = $this->getUserInactive();

        $this->expectException(Exception\InvalidUserActiveStatus::class);

        $user->changePassword($password);
    }

    public function testSameIdentityAs(): void
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $email = Email::fromString($faker->email);
        $password = $faker->password;
        $role = new Role('ROLE_USER');

        $user1 = User::createByAdminMinimum($userId, $email, $password, $role);
        $user2 = User::createByAdminMinimum($userId, $email, $password, $role);

        $this->assertTrue($user1->sameIdentityAs($user2));
    }

    private function getUserActive(): User
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $email = Email::fromString($faker->email);
        $password = $faker->password;
        $role = new Role('ROLE_USER');
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        return User::createByAdmin(
            $userId,
            $email,
            $password,
            $role,
            true,
            $firstName,
            $lastName,
            false
        );
    }

    private function getUserInactive(): User
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $email = Email::fromString($faker->email);
        $password = $faker->password;
        $role = new Role('ROLE_USER');
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        return User::createByAdmin(
            $userId,
            $email,
            $password,
            $role,
            false,
            $firstName,
            $lastName,
            false
        );
    }
}
