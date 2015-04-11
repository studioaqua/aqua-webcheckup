<?php
/**
 *
 */
namespace AppBundle\Controller;

//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Website;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class DefaultController extends Controller
{
    public function newAction(Request $request)
    {
        // create a task and give it some dummy data for this example
        $website = new Website();
        $website->setUrl('www.yourdomain.name');

        $form = $this->createFormBuilder($website)
            ->add('website', 'text')
            ->add('checkup', 'checkup', array('label' => 'Check Up'))
            ->getForm();

        return $this->render('default/new.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
