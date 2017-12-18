<?php

namespace AppBundle\Service;

use AppBundle\Entity\Category;
use AppBundle\Entity\Promotion;

interface PromotionsServiceInterface
{
    public function setPromotionToCategory(Promotion $promotion, Category $category);

    public function setPromotionToProducts(Promotion $promotion);

    public function unsetPromotionToProducts(Promotion $promotion);
}