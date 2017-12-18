<?php

namespace WebShopBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use AppBundle\Entity\Product;
use AppBundle\Entity\User;
use AppBundle\Entity\Cart;
use AppBundle\Entity\Shipping;

class CartService implements CartServiceInterface
{
    private $entityManager;
    private $session;

    public function __construct(
        EntityManagerInterface $entityManager,
        Session $session)
    {
        $this->entityManager = $entityManager;
        $this->session = $session;
    }

    /**
     * @param Product[]|ArrayCollection $products
     * @return float
     */
    public function getProductsTotal($products)
    {
        if ($products->count() == 0) {
            return 0.00;
        }

        $total = array_sum(
            array_map(function (Product $p) {
                return $p->getPrice();
            }, $products->toArray())
        );

        return $total;
    }



}