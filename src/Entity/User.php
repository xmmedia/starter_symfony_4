<?php

declare(strict_types=1);

namespace App\Entity;

use App\Model\User\Name;
use App\Model\User\Role;
use App\Model\User\UserId;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Encoder\EncoderAwareInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Util\StringUtil;

/**
 * @ORM\Entity(repositoryClass="App\Projection\User\UserFinder")
 */
class User implements UserInterface, EncoderAwareInterface, EquatableInterface
{
    /**
     * @var \Ramsey\Uuid\Uuid
     * @ORM\Id
     * @ORM\Column(type="uuid")
     */
    private $userId;

    /**
     * @var Email|string
     * @ORM\Column(type="string", length=150, unique=true)
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $verified = false;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $active = false;

    /**
     * @var array
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastLogin;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $loginCount = 0;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $firstName;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $lastName;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\UserToken", mappedBy="user")
     */
    private $tokens;

    public function __construct()
    {
        $this->tokens = new ArrayCollection();
    }

    public function userId(): UserId
    {
        return UserId::fromUuid($this->userId);
    }

    public function email(): Email
    {
        return Email::fromString($this->email);
    }

    public function getUsername(): string
    {
        return $this->email()->toString();
    }

    public function password(): string
    {
        return $this->password;
    }

    public function getPassword(): string
    {
        return $this->password();
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
        // nothing atm, object never stored un-encoded password
    }

    public function verified(): bool
    {
        return $this->verified;
    }

    public function active(): bool
    {
        return $this->active;
    }

    public function roles(): array
    {
        return $this->roles;
    }

    public function getRoles(): array
    {
        return $this->roles();
    }

    public function firstRole(): Role
    {
        return Role::byValue($this->roles[0]);
    }

    public function lastLogin(): ?\DateTimeImmutable
    {
        if (null === $this->lastLogin) {
            return null;
        }

        return \DateTimeImmutable::createFromMutable($this->lastLogin);
    }

    public function loginCount(): int
    {
        return $this->loginCount;
    }

    public function firstName(): ?Name
    {
        if (null === $this->firstName) {
            return null;
        }

        return Name::fromString($this->firstName);
    }

    public function lastName(): ?Name
    {
        if (null === $this->lastName) {
            return null;
        }

        return Name::fromString($this->lastName);
    }

    public function name(): ?string
    {
        return StringUtil::trim(
            sprintf('%s %s', $this->firstName, $this->lastName)
        );
    }

    public function getEncoderName()
    {
        $adminRoles = ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'];
        if (\count(array_intersect($adminRoles, $this->roles)) > 0) {
            return 'harsh';
        }

        return null;
    }

    /**
     * User will be logged out if their email (username) or password changes,
     * or if they've become inactive or unverified.
     *
     * @param User|UserInterface $user the user just loaded from the db
     */
    public function isEqualTo(UserInterface $user): bool
    {
        if ($this->password !== $user->password()) {
            return false;
        }

        if (!$this->email()->sameValueAs($user->email())) {
            return false;
        }

        if (!$user->active) {
            return false;
        }

        if (!$user->verified) {
            return false;
        }

        return true;
    }
}
