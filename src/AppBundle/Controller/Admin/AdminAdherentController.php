<?php

namespace AppBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin")
 */
class AdminAdherentController extends Controller
{
    /**
     * Exit impersonation.
     *
     * @Route("/impersonation/exit", name="app_admin_impersonation_exit")
     * @Method("GET")
     */
    public function exitImpersonationAction(): Response
    {
        $authUtils = $this->get('app.security.authentication_utils');
        $impersonatingUser = $authUtils->getImpersonatingUser();

        if (!$impersonatingUser) {
            return $this->redirectToRoute('homepage');
        }

        $authUtils->authenticateUser($impersonatingUser);

        return $this->redirectToRoute('admin_app_adherent_list');
    }
}
