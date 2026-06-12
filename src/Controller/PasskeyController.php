<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\WebAuthnCredentialFinder;
use App\Security\WebAuthnUserEntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\Bundle\Security\Storage\Item;
use Webauthn\Bundle\Security\Storage\OptionsStorage;
use Webauthn\Bundle\Service\PublicKeyCredentialCreationOptionsFactory;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;

#[Route('/passkey')]
final class PasskeyController extends AbstractController
{
    public function __construct(
        private readonly WebAuthnUserEntityRepository $userEntityRepository,
        private readonly WebAuthnCredentialFinder $credentialFinder,
        private readonly PublicKeyCredentialCreationOptionsFactory $creationOptionsFactory,
        private readonly OptionsStorage $optionsStorage,
        private readonly SerializerInterface $serializer,
        private readonly AuthenticatorAttestationResponseValidator $attestationValidator,
    ) {
    }

    /**
     * Generates a PublicKeyCredentialCreationOptions challenge and stores it in the session.
     * The frontend passes this to navigator.credentials.create({ publicKey: options }).
     */
    #[Route('/register/options', name: 'passkey_register_options', methods: ['POST'])]
    public function registerOptions(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $user */
        $user = $this->getUser();

        $userEntity = $this->userEntityRepository->findOneByUsername($user->getUserIdentifier());
        if (null === $userEntity) {
            return new JsonResponse(['error' => 'User not found.'], Response::HTTP_NOT_FOUND);
        }

        $excludedCredentials = $this->credentialFinder->findAllForUserEntity($userEntity);
        $descriptors = array_map(
            static fn ($source): \Webauthn\PublicKeyCredentialDescriptor => $source->getPublicKeyCredentialDescriptor(),
            $excludedCredentials,
        );

        $options = $this->creationOptionsFactory->create('default', $userEntity, $descriptors);

        $this->optionsStorage->store(Item::create($options, $userEntity));

        return new JsonResponse(
            json_decode($this->serializer->serialize($options, JsonEncoder::FORMAT), true),
        );
    }

    /**
     * Validates the attestation response and persists the new passkey.
     * Expects JSON body: { ...WebAuthn credential..., "name": "optional label" }.
     */
    #[Route('/register', name: 'passkey_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $user */
        $user = $this->getUser();

        $content = $request->getContent();
        $body = json_decode($content, true, 512, \JSON_THROW_ON_ERROR);

        $name = isset($body['name']) ? (string) $body['name'] : null;

        // Remove name before deserializing as PublicKeyCredential.
        unset($body['name']);
        $credentialJson = json_encode($body, \JSON_THROW_ON_ERROR);

        $publicKeyCredential = $this->serializer->deserialize($credentialJson, PublicKeyCredential::class, JsonEncoder::FORMAT);
        $response = $publicKeyCredential->response;

        if (!$response instanceof AuthenticatorAttestationResponse) {
            return new JsonResponse(['error' => 'Invalid response type.'], Response::HTTP_BAD_REQUEST);
        }

        $storedItem = $this->optionsStorage->get($response->clientDataJSON->challenge);
        $options = $storedItem->getPublicKeyCredentialOptions();

        if (!$options instanceof PublicKeyCredentialCreationOptions) {
            return new JsonResponse(['error' => 'No creation options found in session.'], Response::HTTP_BAD_REQUEST);
        }

        $credentialSource = $this->attestationValidator->check($response, $options, $request->getHost());

        if (null !== $this->credentialFinder->findOneByCredentialId($credentialSource->publicKeyCredentialId)) {
            return new JsonResponse(['error' => 'This passkey is already registered.'], Response::HTTP_CONFLICT);
        }

        $this->credentialFinder->createCredential($user, $credentialSource, $name);

        return new JsonResponse(['success' => true]);
    }
}
