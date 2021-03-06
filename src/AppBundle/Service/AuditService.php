<?php
namespace AppBundle\Service;

use AppBundle\Entity\Action;
use AppBundle\Entity\TableHistory;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AuditService
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
    }

    public function add(Action $action, ?TableHistory $history = null)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        if ($history) {
            $history->setUser($user);
            $this->em->persist($history);
            $this->em->flush();
            $action->setTableHistory($history);
        }

        $action->setUser($user);
        $this->em->persist($action);
        $this->em->flush();
    }

    /**
     * @param Action $action
     * @return null|object
     */
    public function getEntity(Action $action)
    {
        if ($history = $action->getTableHistory()) {
            $class = $history->getTable();
            $id = $history->getEntry();
            if (!class_exists($class)) {
                return null;
            }
            return $this->em->getRepository($class)->find($id);
        } elseif (substr($action->getAction(), 0, 7) === 'profile' || $action->getAction() === 'user-added') {
            return $this->em->getRepository(User::class)->find($action->getData1());
        } else {
            return null;
        }
    }
}
