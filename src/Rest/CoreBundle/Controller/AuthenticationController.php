<?php

namespace Kunstmaan\Rest\CoreBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\Model\UserInterface;
use Kunstmaan\AdminBundle\FlashMessages\FlashTypes;
use Kunstmaan\AdminBundle\Repository\UserRepository;
use Kunstmaan\Rest\CoreBundle\Entity\HasApiKeyInterface;
use Kunstmaan\Rest\CoreBundle\Helper\GenerateApiKeyFunctionTrait;
use Kunstmaan\UserManagementBundle\Event\UserEvents;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class NodesController
 */
class AuthenticationController extends AbstractController
{
    use GenerateApiKeyFunctionTrait;

    /** @var EntityManagerInterface */
    private $em;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var string */
    private $userClass;

    public function __construct(EntityManagerInterface $em, EventDispatcherInterface $eventDispatcher, string $userClass)
    {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
        $this->userClass = $userClass;
    }

    /**
     * @param Request $request
     * @param int     $id
     *
     * @Route("/{id}/generate-key", requirements={"id" = "\d+"}, name="KunstmaanRestCoreBundle_settings_users_key_generate", methods={"GET"})
     *
     * @throws AccessDeniedException
     * @throws BadRequestHttpException
     * @return RedirectResponse
     *
     * @throws \Exception
     */
    public function generateKeyAction(Request $request, $id)
    {
        // The logged in user should be able to change his own generated api key and not for other users
        if ((int) $id === (int) $this->get('security.token_storage')->getToken()->getUser()->getId()) {
            $requiredRole = 'ROLE_ADMIN';
        } else {
            $requiredRole = 'ROLE_SUPER_ADMIN';
        }
        $this->denyAccessUnlessGranted($requiredRole);
        /* @var $em EntityManager */
        $em = $this->em;
        /** @var UserRepository $repo */
        $repo = $em->getRepository($this->userClass);
        /* @var UserInterface $user */
        $user = $repo->find($id);
        if (!$user instanceof HasApiKeyInterface) {
            throw new BadRequestHttpException('user needs to have api key implemented');
        }
        if ($user !== null) {
            $userEvent = new UserEvent($user, $request);
            $this->eventDispatcher->dispatch(UserEvents::USER_EDIT_INITIALIZE, $userEvent);
            $user->setApiKey($this->generateApiKey());
            $em->flush();
            $this->addFlash(
                FlashTypes::SUCCESS,
                $this->get('translator')->trans('kuma_user.users.key_generate.flash.success.%username%', [
                    '%username%' => $user->getUsername(),
                ])
            );
        }

        return new RedirectResponse(
            $this->generateUrl(
                'KunstmaanUserManagementBundle_settings_users_edit',
                ['id' => $id]
            )
        );
    }
}
