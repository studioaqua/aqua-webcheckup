<?php

namespace Aqua\WebCheckupBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Aqua\WebCheckupBundle\Entity\Website;
//use Aqua\WebCheckupBundle\Model\WebRank;

class DefaultController extends Controller
{
  protected $logger;

  public function indexAction(Request $request)
  {
    // Get Logger.
    $logger = $this->get('logger');

    // create a task and give it some dummy data for this example
    $website = new Website();
    //$website->setWebsite('http://www.yourdomain.name');

    $form = $this->createFormBuilder($website)
        ->add('website', 'text')
        ->add('checkup', 'submit', array('label' => 'Check Up'))
        ->getForm();

    $form->handleRequest($request);

    if ($form->isValid())
    {
      // Set the typed url.
      $website->setWebsite($form->get('website')->getData());

      $logger->debug('Check url @url',
              array('@url' => $website->getWebsite()));

      // Get buzz browser.
      $buzz = $this->container->get('buzz');
      $response = $buzz->get($website->getWebsite());
      /*
      $logger->debug('Response => @content',
              array('@content' => var_export($response, TRUE)));
      */

      if ($response->getStatusCode() == 200) {

        $website->setHtmlSource($response->getContent());

        // Calculate the rank for this URL.
        // The URL is the elemet with index = 1
        $webrank = $this->get('web_rank');

        $webrank->runCheckup($website);
        /*
        $logger->info('Result {website}', array(
            'website' => var_export($website, TRUE)
          )
        );
        */
        return $this->render(
          'AquaWebCheckupBundle:Default:results.html.twig',
          array(
            'website' => $website,
          )
        );
      }
      else
      {
        $logger->error($response->getContent());
      }
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
