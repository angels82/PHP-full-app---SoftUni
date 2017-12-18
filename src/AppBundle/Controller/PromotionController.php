<?php

namespace AppBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use function Symfony\Component\Debug\Tests\testHeader;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Product;
use AppBundle\Entity\Promotion;

class PromotionController extends Controller
{
    /**
     * @Route("/promotions", name="promotions_list")
     *
     * @param Request $request
     * @return Response
     */
    public function listPromotionsAction(Request $request)
    {
        //$pager = $this->get('knp_paginator');
        /** @var Promotion[]|ArrayCollection $promotions */
        $promotions = $this->getDoctrine()->getRepository(Promotion::class)
                ->findNotExpiredQueryBuilder();


        //echo '<pre>'; var_dump($promotions); die();

        return $this->render("promotions/list.html.twig", [
            "promotions" => $promotions
        ]);
    }

    /**
     * @Route("/promotions/view/{id}", name="promotions_view")
     *
     * @param Promotion $promotion
     * @param Request $request
     * @return Response
     */
    public function viewPromotionAction(Promotion $promotion, Request $request)
    {

        /** @var ArrayCollection|Product[] $products */
        $products = $promotion->getProductsWithActivePromo();

        return $this->render("promotions/view.html.twig", [
            "promotion" => $promotion,
            "products" => $products
        ]);
    }
}
