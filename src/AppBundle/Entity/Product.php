<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Product
 *
 * @ORM\Table(name="product")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProductRepository")
 */
class Product
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    private $description;
    /**
     * @var string
     *
     * @ORM\Column(name="owner", type="string", length=255)
     */
    private $owner;
    /**
     * @var string
     * @ORM\Column(name="selling", type="string", length=255)
     */
    private $selling;

    /**
     * @return string
     */
    public function getOwner(): string
    {
        return $this->owner;
    }

    /**
     * @param string $owner
     */
    public function setOwner(string $owner)
    {
        $this->owner = $owner;
    }


    /**
     * @ORM\Column(name="imageFile", type="string")
     * @Assert\Image
     */
    private $imageFile;

    /**
     * @var int
     *
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=5, scale=2)
     */
    private $price;



    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Category")

     */
    private $category;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="product")

     */
    private $user;

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Promotion", inversedBy="products")
     * @ORM\JoinTable(name="product_promotions")
     *
     * @var ArrayCollection
     */
    private $promotions;

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return Category
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * @param Category $category
     */
    public function setCategory(Category $category)
    {
        $this->category = $category;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return product
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return product
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set imageName
     *
     * @param string $imageName
     *
     * @return product
     */



    public function setImageFile($imageFile)
    {
        $this->imageFile = $imageFile;

        return $this;
    }


    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return product
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set price
     *
     * @param string $price
     *
     * @return product
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice()
    {
        if ($this->hasActivePromotion()) {
            $discount = $this->price * $this->getActualPromotion()->getDiscount() / 100;
            return $this->price - $discount;
        }

        return $this->price;
    }

    public function __construct()
    {
        $this->promotions = new ArrayCollection();
    }

    public function getPromotions()
    {
        return $this->promotions;
    }

    public function setPromotions($promotions)
    {
        $this->promotions = $promotions;

        return $this;
    }

    public function setPromotion(Promotion $promotion)
    {
        $this->promotions[] = $promotion;
    }

    public function unsetPromotion(Promotion $promotion)
    {
        $this->promotions->removeElement($promotion);
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return Promotion|null
     */
    public function getActualPromotion()
    {
        $activePromotions = $this->promotions->filter(
            function (Promotion $p) {
                return $p->getEndDate() > new \DateTime("now") &&
                    $p->getStartDate() <= new \DateTime("now");
            });

        if ($activePromotions->count() == 0) {
            return null;
        }

        if ($activePromotions->count() == 1) {
            return $activePromotions->first();
        }

        $arr = $activePromotions->getValues();
        usort($arr, function (Promotion $p1, Promotion $p2) {
            return $p2->getDiscount() - $p1->getDiscount();
        });

        return $arr[0];
    }

    /**
     * @return float
     */
    public function getOriginalPrice()
    {
        return $this->price;
    }

    /**
     * @return bool
     */
    public function hasActivePromotion()
    {
        return $this->getActualPromotion() !== null;
    }


    /**
     * @return mixed
     */
    public function getSelling()
    {
        return $this->selling;
    }

    /**
     * @param mixed $selling
     */
    public function setSelling($selling)
    {
        $this->selling = $selling;
    }
}

