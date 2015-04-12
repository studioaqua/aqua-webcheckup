<?php

namespace Aqua\WebCheckupBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Aqua\WebCheckupBundle\Entity\Website;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        // create a task and give it some dummy data for this example
        $website = new Website();
        $website->setWebsite('www.yourdomain.name');

        $form = $this->createFormBuilder($website)
            ->add('website', 'text')
            ->add('checkup', 'submit', array('label' => 'Check Up'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            // perform some action, such as saving the task to the database

            return $this->redirectToRoute('aqua_web_checkup_success');
        }

        return $this->render('AquaWebCheckupBundle:Default:index.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
