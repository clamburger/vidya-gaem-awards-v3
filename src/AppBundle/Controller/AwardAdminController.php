<?php
namespace AppBundle\Controller;

use AppBundle\Entity\Action;
use AppBundle\Entity\Autocompleter;
use AppBundle\Entity\Award;
use AppBundle\Entity\TableHistory;
use AppBundle\Service\ConfigService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AwardAdminController extends Controller
{
    public function managerListAction(EntityManagerInterface $em, AuthorizationCheckerInterface $authCheck, Request $request)
    {
        $query = $em->createQueryBuilder()
            ->select('a')
            ->from(Award::class, 'a');

        if (!$authCheck->isGranted('ROLE_AWARDS_SECRET')) {
            $query->andWhere('a.secret = false');
        }
        $query
            ->addOrderBy('a.enabled', 'DESC')
            ->addOrderBy('a.order', 'ASC')
            ->indexBy('a', 'a.id');
        $awards = $query->getQuery()->getResult();

        if ($request->get('sort') === 'percentage') {
            uasort($awards, function (Award $a, Award $b) {
                return $b->getFeedbackPercent()['positive'] <=> $a->getFeedbackPercent()['positive'];
            });
        } elseif ($request->get('sort') === 'net') {
            uasort($awards, function (Award $a, Award $b) {
                return $b->getGroupedFeedback()['net'] <=> $a->getGroupedFeedback()['net'];
            });
        }
        
        $autocompleters = $em->getRepository(Autocompleter::class)->findAll();

        return $this->render('awardManager.twig', [
            'title' => 'Award Manager',
            'awards' => $awards,
            'autocompleters' => $autocompleters
        ]);
    }

    public function managerPostAction(EntityManagerInterface $em, Request $request, ConfigService $configService, UserInterface $user)
    {
        $post = $request->request;

        if ($configService->isReadOnly()) {
            $this->addFlash('formError', 'The site is currently in read-only mode. No changes can be made.');
            return $this->redirectToRoute('awardManager');
        }

        // Open / close all awards
        if ($post->get('action') === 'massChangeNominations') {
            $query = $em->createQueryBuilder()->from(Award::class, 'a');

            if ($post->get('todo') === 'open') {
                $query->update()->set('a.nominationsEnabled', true);
                $query->getQuery()->execute();

                $action = new Action('mass-nomination-change');
                $action->setUser($user)
                    ->setPage(__CLASS__)
                    ->setData1('open');
                $em->persist($action);
                $this->addFlash('formSuccess', 'Nominations for all awards are now open.');
            } elseif ($post->get('todo') === 'close') {
                $query->update()->set('a.nominationsEnabled', 0);
                $query->getQuery()->execute();

                $action = new Action('mass-nomination-change');
                $action->setUser($user)
                    ->setPage(__CLASS__)
                    ->setData1('close');
                $em->persist($action);
                $this->addFlash('formSuccess', 'Nominations for all awards are now closed.');
            }

            $em->flush();
        }

        return $this->redirectToRoute('awardManager');
    }

    public function managerPostAjaxAction(EntityManagerInterface $em, Request $request, ConfigService $configService, UserInterface $user, AuthorizationCheckerInterface $authChecker)
    {
        if ($configService->isReadOnly()) {
            return $this->json(['error' => 'The site is currently in read-only mode. No changes can be made.']);
        }

        $post = $request->request;

        if (strlen($post->get('id')) == 0) {
            return $this->json(['error' => 'An ID is required.']);
        }

        $award = $em->getRepository(Award::class)->find($post->get('id'));
        if ($award && $post->get('action') === 'new') {
            return $this->json(['error' => 'That ID is already in use. Please enter another ID.']);
        } elseif ((!$award || ($award->isSecret() && !$authChecker->isGranted('ROLE_AWARDS_SECRET'))) && $post->get('action') === 'edit') {
            return $this->json(['error' => 'Couldn\'t find an award with that ID.']);
        }

        if ($post->get('action') === 'delete') {
            if ($authChecker->isGranted('ROLE_AWARDS_DELETE')) {
                $em->remove($award);
                $em->flush();

                $action = new Action('award-delete');
                $action->setUser($user)
                    ->setPage(__CLASS__)
                    ->setData1($award->getId());
                $em->persist($action);
                $em->flush();

                return $this->json(['success' => true]);
            } else {
                return $this->json(['error' => 'You aren\'t allowed to delete awards.']);
            }
        } elseif ($post->get('action') === 'new' || $post->get('action') === 'edit') {
            if (!$award) {
                $award = new Award();
                try {
                    $award->setId($post->get('id'));
                } catch (\Exception $e) {
                    return $this->json(['error' => 'Invalid award ID provided.']);
                }
            }

            if (strlen($post->get('name')) === 0) {
                return $this->json(['error' => 'An award name is required.']);
            } elseif (strlen($post->get('subtitle')) === 0) {
                return $this->json(['error' => 'A subtitle is required.']);
            } elseif (!ctype_digit($post->get('order')) || $post->get('order') > 10000) {
                return $this->json(['error' => 'Position must be between 1 and 10000.']);
            }

            if ($post->get('autocompleter')) {
                $autocompleter = $em->getRepository(Autocompleter::class)->find($post->get('autocompleter'));
                if (!$autocompleter) {
                    $autocompleter = null;
                }
            } else {
                $autocompleter = null;
            }

            $award
                ->setName($post->get('name'))
                ->setSubtitle($post->get('subtitle'))
                ->setComments($post->get('comments'))
                ->setAutocompleter($autocompleter)
                ->setOrder($post->getInt('order'))
                ->setEnabled((bool)$post->get('enabled'))
                ->setNominationsEnabled((bool)$post->get('nominationsEnabled'))
                ->setSecret((bool)$post->get('secret'));

            $em->persist($award);

            $action = new Action($post->get('action') === 'new' ? 'award-added' : 'award-edited');
            $action->setUser($user)
                ->setPage(__CLASS__)
                ->setData1($post->get('id'));
            $em->persist($action);

            $history = new TableHistory();
            $history->setUser($user)
                ->setTable('Award')
                ->setEntry($post->get('id'))
                ->setValues($post->all());
            $em->persist($history);
            $em->flush();

            return $this->json(['success' => true]);
        } else {
            return $this->json(['error' => 'Invalid action specified.']);
        }
    }
}