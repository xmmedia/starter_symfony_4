<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Document\Page;
use App\Security\Security;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class CreatedUpdatedBySubscriber implements EventSubscriber
{
    /** @var Security */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function prePersist(LifecycleEventArgs $e): void
    {
        $this->setCreatedBy($e);
    }

    public function preUpdate(LifecycleEventArgs $e): void
    {
        $this->setLastModified($e);
    }

    private function setCreatedBy(LifecycleEventArgs $e): void
    {
        $page = $e->getObject();

        if (!$page instanceof Page) {
            return;
        }

        if (null === $user = $this->security->getUser()) {
            return;
        }

        $page->setCreatedBy($user->userId());
    }

    private function setLastModified(LifecycleEventArgs $e): void
    {
        $page = $e->getObject();

        if (!$page instanceof Page) {
            return;
        }

        if (null === $user = $this->security->getUser()) {
            return;
        }

        $page->setLastModified(new \DateTime());
        $page->setLastModifiedBy($user->userId());
    }
}
