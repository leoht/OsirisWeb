<?php

namespace Osiris\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class PlayerController extends Controller
{
	/**
	 * @Route("/player", name="app_player")
	 */
	public function indexAction(Request $request)
	{
	    return $this->render('OsirisAppBundle:Player:index.html.twig', array());
	}
}
