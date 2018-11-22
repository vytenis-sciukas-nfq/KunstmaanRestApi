<?php

namespace Kunstmaan\Rest\CoreBundle\Controller;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\Model\UserInterface;
use Kunstmaan\AdminBundle\Controller\BaseSettingsController;
use Kunstmaan\AdminBundle\FlashMessages\FlashTypes;
use Kunstmaan\AdminBundle\Repository\UserRepository;
use Kunstmaan\Rest\CoreBundle\Entity\HasApiKeyInterface;
use Kunstmaan\Rest\NodeBundle\Form\RestNodeType;
use Kunstmaan\UserManagementBundle\Event\UserEvents;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class NodesController
 */
class AuthenticationController extends BaseSettingsController
{
    /**
     * Delete a user
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/{id}/generate-key", requirements={"id" = "\d+"}, name="KunstmaanRestCoreBundle_settings_users_key_generate")
     * @Method({"GET"})
     *
     * @throws AccessDeniedException
     * @throws BadRequestHttpException
     * @return RedirectResponse
     */
    public function generateKeyAction(Request $request, $id)
    {
        // The logged in user should be able to change his own generated api key and not for other users
        if ($id == $this->get('security.token_storage')->getToken()->getUser()->getId()) {
            $requiredRole = 'ROLE_ADMIN';
        } else {
            $requiredRole = 'ROLE_SUPER_ADMIN';
        }
        $this->denyAccessUnlessGranted($requiredRole);
        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();
        /** @var UserRepository $repo */
        $repo = $em->getRepository($this->getParameter('fos_user.model.user.class'));
        /* @var UserInterface $user */
        $user = $repo->find($id);
        if (!$user instanceof HasApiKeyInterface) {
            throw new BadRequestHttpException('user needs to have api key implemented');
        }
        if (!is_null($user)) {
            $userEvent = new UserEvent($user, $request);
            $this->container->get('event_dispatcher')->dispatch(UserEvents::USER_EDIT_INITIALIZE, $userEvent);
            $user->setApiKey(Uuid::uuid5(Uuid::NAMESPACE_URL, 'apikey'));
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
