<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Entity\WebAuthnCredential;
use App\Projection\User\UserFinder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Webauthn\Bundle\Repository\CanSaveCredentialSource;
use Webauthn\Bundle\Repository\PublicKeyCredentialSourceRepositoryInterface;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialUserEntity;

/**
 * @extends ServiceEntityRepository<WebAuthnCredential>
 */
class WebAuthnCredentialFinder extends ServiceEntityRepository implements PublicKeyCredentialSourceRepositoryInterface, CanSaveCredentialSource
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly SerializerInterface $serializer,
        private readonly UserFinder $userFinder,
    ) {
        parent::__construct($registry, WebAuthnCredential::class);
    }

    /**
     * @return PublicKeyCredentialSource[]
     */
    public function findAllForUserEntity(PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity): array
    {
        $user = $this->userFinder->find($publicKeyCredentialUserEntity->id);
        if (null === $user) {
            return [];
        }

        return array_map(
            fn (WebAuthnCredential $c): PublicKeyCredentialSource => $this->deserializeSource($c->credentialSourceJson()),
            $this->findBy(['user' => $user]),
        );
    }

    public function findOneByCredentialId(string $publicKeyCredentialId): ?PublicKeyCredentialSource
    {
        $credential = $this->findOneBy(['publicKeyCredentialId' => base64_encode($publicKeyCredentialId)]);

        if (null === $credential) {
            return null;
        }

        return $this->deserializeSource($credential->credentialSourceJson());
    }

    /**
     * Called by the bundle during the authentication ceremony to update the counter.
     * For new credential creation, use createCredential() instead to support naming.
     */
    public function saveCredentialSource(PublicKeyCredentialSource $publicKeyCredentialSource): void
    {
        $encoded = base64_encode($publicKeyCredentialSource->publicKeyCredentialId);
        $credential = $this->findOneBy(['publicKeyCredentialId' => $encoded]);

        if (null !== $credential) {
            $credential->updateCredentialSource($this->serializeSource($publicKeyCredentialSource));
            $this->getEntityManager()->flush();

            return;
        }

        // New credential via bundle's attestation controller (name will be null).
        $user = $this->userFinder->find($publicKeyCredentialSource->userHandle);
        if (null === $user) {
            return;
        }

        $this->persistCredential($user, $publicKeyCredentialSource, null);
    }

    public function createCredential(User $user, PublicKeyCredentialSource $source, ?string $name): WebAuthnCredential
    {
        return $this->persistCredential($user, $source, $name);
    }

    /**
     * @return WebAuthnCredential[]
     */
    public function findAllForUser(User $user): array
    {
        return $this->findBy(['user' => $user], ['createdAt' => 'ASC']);
    }

    public function findOneByIdAndUser(string $credentialId, User $user): ?WebAuthnCredential
    {
        return $this->findOneBy(['credentialId' => $credentialId, 'user' => $user]);
    }

    public function remove(WebAuthnCredential $credential): void
    {
        $this->getEntityManager()->remove($credential);
        $this->getEntityManager()->flush();
    }

    private function persistCredential(User $user, PublicKeyCredentialSource $source, ?string $name): WebAuthnCredential
    {
        $credential = WebAuthnCredential::create($user, $source, $this->serializeSource($source), $name);
        $this->getEntityManager()->persist($credential);
        $this->getEntityManager()->flush();

        return $credential;
    }

    private function serializeSource(PublicKeyCredentialSource $source): string
    {
        return $this->serializer->serialize($source, JsonEncoder::FORMAT);
    }

    private function deserializeSource(string $json): PublicKeyCredentialSource
    {
        return $this->serializer->deserialize($json, PublicKeyCredentialSource::class, JsonEncoder::FORMAT);
    }
}
