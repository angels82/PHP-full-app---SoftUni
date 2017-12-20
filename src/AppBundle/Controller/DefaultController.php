<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     *
     */
    public function indexAction(Request $request)
    {
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        if ($user != 'anon.' and $user->getRoles()[0] !='ROLE_ADMIN') {
            $user_cart = $this->getDoctrine()
                ->getRepository('AppBundle:Cart')
                ->findBy(['user' => $user]);
            if ( $user_cart )
            {
                $user_products = $this->getDoctrine()
                    ->getRepository('AppBundle:Shipping')
                    ->findBy( array('cart' => $user_cart[0]->getId()) );
                $count = 0;
                foreach ($user_products as $item) {
                    $count++;
                }
            }


            return $this->render('base.html.twig', [
                'base_dir' => realpath($this->getParameter('kernel.root_dir') . '/..') . DIRECTORY_SEPARATOR
                ]);
        }
        return $this->render('base.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir') . '/..') . DIRECTORY_SEPARATOR,
            "count" => 0]);
    }

    /**
     * @Route("/profile", name="profile_view")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewProfile(Request $request)
    {
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $user_cart = $this->getDoctrine()
            ->getRepository('AppBundle:Cart')
            ->findBy(['user' => $user]);
        if ( $user_cart )
        {
            $user_products = $this->getDoctrine()
                ->getRepository('AppBundle:Shipping')
                ->findBy( array('cart' => $user_cart[0]->getId()) );
            $count = 0;
            foreach ($user_products as $item) {
                $count++;
            }
        }
        return $this->render("user/profile_view.html.twig", [
            "count"=>$count]);
    }
}
