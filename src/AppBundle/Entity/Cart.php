<?php
namespace AppBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use AppBundle\Entity\Shipping;

#use AppBundle\Entity\User;
#use AppBundle\Entity\Product;
/**
 * @ORM\Entity
 * @ORM\Table(name="cart")
 */
class Cart
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(type="decimal", scale=2)
     */
    private $total_price;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantity;
    /**
     * @ORM\OneToOne(targetEntity="User", inversedBy="cart")
     */
    private $user;
    /** @ORM\OneToMany(targetEntity="Shipping", mappedBy="cart") */
    protected $cartProducts;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->cartProducts = new ArrayCollection();
    }
    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * Set total_price
     *
     * @param string $totalPrice
     * @return Cart
     */
    public function setTotalPrice($totalPrice)
    {
        $this->total_price = $totalPrice;
        return $this;
    }
    /**
     * Get total_price
     *
     * @return string
     */
    public function getTotalPrice()
    {
        return $this->total_price;
    }
    /**
     * Set quantity
     *
     * @param integer $quantity
     * @return Cart
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }
    /**
     * Get quantity
     *
     * @return integer
     */
    public function getQuantity()
    {
        return $this->quantity;
    }
    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return Cart
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;
        return $this;
    }
    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add cartProducts
     *
     * @param \AppBundle\Entity\Shipping|array $cartProducts
     * @return Collection
     */
    public function addCartProduct(array $cartProducts)
    {
        $this->cartProducts[] = $cartProducts;
        return $this;
    }
    /**
     * Remove cartProducts
     *
     * @param Shipping $cartProducts
     */
    public function removeCartProduct(array $cartProducts)
    {
        $this->cartProducts->removeElement($cartProducts);
    }
    /**
     * Get cartProducts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCartProducts()
    {
        return $this->cartProducts;
    }
}