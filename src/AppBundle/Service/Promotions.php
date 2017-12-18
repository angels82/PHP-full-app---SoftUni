<?php

namespace AppBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Product;
use AppBundle\Entity\Category;
use AppBundle\Entity\Promotion;


class Promotions implements PromotionsServiceInterface
{


    public function setPromotionToCategory(Promotion $promotion, Category $category)
    {
        /** @var ArrayCollection|Product[] $products */
        $products = $category->getProducts();
        foreach ($products as $product) {
            if ($product->getPromotions()->contains($promotion)) {
                continue;
            }

            $product->setPromotion($promotion);
        }

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $this->session
            ->getFlashBag()
            ->add("success",
                "All products of category '{$category->getName()}' was promoted with '{$promotion->getName()}' promotion");
    }

    public function setPromotionToProducts(Promotion $promotion)
    {
        $allProducts = $this->manager->getRepository(Product::class)
            ->findAll();

        foreach ($allProducts as $product) {
            /*if ($product->getPromotions()->contains($promotion)) {
                continue;
            }*/

            $product->setPromotion($promotion);
        }

        $this->entityManager->persist($promotion);
        $this->entityManager->flush();

        $this->session
            ->getFlashBag()
            ->add("success", "All products was promoted with '{$promotion->getName()}' promotion");
    }

    public function unsetPromotionToProducts(Promotion $promotion)
    {
        $allProducts = $this->manager->getRepository(Product::class)
            ->findAll();

        foreach ($allProducts as $product) {
            /*if (!$product->getPromotions()->contains($promotion)) {
                continue;
            }*/

            $product->unsetPromotion($promotion);
        }

        $this->entityManager->persist($promotion);
        $this->entityManager->flush();

        $this->session
            ->getFlashBag()
            ->add("success", "'{$promotion->getName()}' promotion was removed from all products!");
    }
}