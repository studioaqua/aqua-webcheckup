<?php

namespace Aqua\WebCheckupBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Aqua\WebCheckupBundle\Entity\Website;
use Aqua\WebCheckupBundle\Model\WebRank;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

class DefaultController extends Controller
{
  protected $logger;

  public function indexAction(Request $request)
  {
    // create a task and give it some dummy data for this example
    $website = new Website();
    $website->setWebsite('http://www.yourdomain.name');

    $form = $this->createFormBuilder($website)
        ->add('website', 'text')
        ->add('checkup', 'submit', array('label' => 'Check Up'))
        ->getForm();

    $form->handleRequest($request);

    if ($form->isValid())
    {
      // Set the typed url.
      $website->setWebsite($form->get('website')->getData());

      // Get buzz browser.
      $buzz = $this->container->get('buzz');
      //$response = $buzz->get($website);
      //echo $response->getContent();

      // Get Logger.
      $logger = $this->get('logger');

      // Calculate the rank for this URL.
      // The URL is the elemet with index = 1
      $webrank = new WebRank($website, $buzz, $logger);

      //return $this->redirectToRoute('aqua_web_checkup_success');
    }

    return $this->render('AquaWebCheckupBundle:Default:index.html.twig', array(
        'form' => $form->createView(),
    ));
  }

  public function resultsAction()
  {
    return $this->render('AquaWebCheckupBundle:Default:results.html.twig');
  }
}
