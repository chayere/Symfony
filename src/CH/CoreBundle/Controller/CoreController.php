<?php

namespace CH\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class CoreController extends Controller
{
    public function indexAction()
    {
        return $this->render('CHCoreBundle:Core:index.html.twig');
    }

    public function contactAction(Request $request) {

    	$request->getSession()->getFlashBag()->add('info', "La page de contact n'est pas encore disponible, merci de revenir plus tard.");

    	return $this->redirectToRoute('ch_core_home');
    }
}
