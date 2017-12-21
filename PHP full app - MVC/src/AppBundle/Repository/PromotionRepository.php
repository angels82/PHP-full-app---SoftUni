<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * PromotionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PromotionRepository extends EntityRepository
{

    public function findNotExpiredQueryBuilder()
    {
        $query = $this->createQueryBuilder("promotion")
            ->join("promotion.products", "products")
            ->addSelect("products")
            ->andWhere("promotion.endDate >= :now")
            ->andWhere("promotion.startDate <= :now")
            ->setParameter("now", new \DateTime("now"))->getQuery();
        $arr = $query->getResult();
        return $arr;
    }

    /**
     * @return QueryBuilder
     */
    public function findByQueryBuilder()
    {
        $query = $this->createQueryBuilder("promotion")
            ->join("promotion.products", "products")
            ->addSelect("products")->getQuery();
        $arr = $query->getResult();
        return $arr;
    }
}
