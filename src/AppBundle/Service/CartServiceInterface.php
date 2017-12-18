<?php

namespace WebShopBundle\Service;

use AppBundle\Entity\Product;
use AppBundle\Entity\User;
use AppBundle\Entity\Cart;
use AppBundle\Entity\Shipping;

interface CartServiceInterface
{
    public function getProductsTotal($products);

    public function checkoutCart(User $user, Cart $cart);
}