<?php

namespace Aqua\WebCheckupBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Aqua\WebCheckupBundle\Entity\Website;

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
    ->add('checkup', 'submit', array('label' => 'Check It!'))
    ->getForm();


    $form->handleRequest($request);

    if ($form->isValid())
    {
      // Set the typed url.
      $website->setWebsite($form->get('website')->getData());

      $logger->debug('Check url @url',
          array('@url' => $website->getWebsite()));

      // Calculate the rank for this URL.
      // The URL is the elemet with index = 1
      $webrank = $this->get('web_rank');

      $webrank->runCheckup($website);

      $em = $this->getDoctrine()->getManager();
      $em->persist($website);
      $em->flush();

      return $this->render(
        'AquaWebCheckupBundle:Default:result.html.twig',
        array(
          'website' => $website,
          )
        );

    }
    else
    {
      return $this->render('AquaWebCheckupBundle:Default:index.html.twig', array(
          'form' => $form->createView(),
        )
      );
    }
  }

  /**
   * [aboutAction description]
   * @return [type] [description]
   */
  public function aboutAction()
  {
    return $this->render('AquaWebCheckupBundle:Default:about.html.twig');
  }

  public function resultAction(Request $request)
  {
    $report_id = $request->query->get('id');

    if (!empty($report_id)
      && is_numeric($report_id))
    {
      $report = $this->getDoctrine()
        ->getRepository('AquaWebCheckupBundle:Website')
        ->find($report_id);

      if (!$report) {
        throw $this->createNotFoundException(
          'No report found for id ' . $report_id
        );
      }

      // Create the HTML report.
      return $this->render(
        'AquaWebCheckupBundle:Default:result.html.twig',
        array(
          'website' => $report,
        )
      );
    }
    else
    {
      return $this->render('AquaWebCheckupBundle:Default:index.html.twig', array());
    }
  }

  public function downloadAction(Request $request)
  {
    $report_id = $request->query->get('id');

    $report = $this->getDoctrine()
        ->getRepository('AquaWebCheckupBundle:Website')
        ->find($report_id);

    if (!$report) {
      throw $this->createNotFoundException(
        'No report found for id ' . $report_id
      );
    }

    return $this->render(
      'AquaWebCheckupBundle:Default:download.html.twig',
      array(
        'website' => $report,
        )
      );
/*
    // Create the HTML report.
    $html = $this->renderView(
      'AquaWebCheckupBundle:Default:download.html.twig',
      array(
        'website' => $report,
      )
    );

    $this->get('logger')->debug('HTML result @html',
          array('@html' => $html));

    $filename = 'report_' . $report->getWebsiteId();
    $snappy = $this->get('knp_snappy.pdf');

    $this->get('logger')->debug('Command @cmd',
            array('@cmd' => $snappy->getInternalGenerator()->getCommand($html, $filename)
              ));

    return new Response(
      $snappy->getOutputFromHtml($html),
        200,
        array(
          'Content-Type'          => 'application/pdf',
          'Content-Disposition'   => 'attachment; filename="' . $filename . '"'
        )
      );
*/
  }

}
