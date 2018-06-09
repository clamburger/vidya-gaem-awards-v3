<?php
namespace AppBundle\Controller;

use AppBundle\Entity\Action;
use AppBundle\Service\AuditService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AuditLogController extends Controller
{
    public function indexAction(EntityManagerInterface $em, AuditService $auditService)
    {
        $actions = [
            'profile-group-added' => 'Added a permission to a user',
            'profile-group-removed' => 'Removed a permission from a user',
            'profile-notes-updated' => 'Updated user notes',
            'profile-details-updated' => 'Updated user details',
            'user-added' => 'Added a user to the team',
            'config-updated' => 'Updated the website config',
            'sentiment-rules-updated' => 'Updated the sentiment rules',
        ];

        $result = $em->createQueryBuilder()
            ->select('a')
            ->from(Action::class, 'a')
            ->where('a.user IS NOT NULL')
            ->andWhere('a.action IN (:actions)')
            ->setParameter('actions', array_keys($actions))
            ->orderBy('a.timestamp', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('auditLog.html.twig', [
            'title' => 'Audit Log',
            'actions' => $result,
            'actionTypes' => $actions,
            'auditService' => $auditService,
        ]);
    }
}
