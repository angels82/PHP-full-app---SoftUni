<?php

namespace AppBundle\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Product;
use AppBundle\Entity\Cart;
use AppBundle\Entity\User;
use AppBundle\Entity\Shipping;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * @property  session
 * @Security("has_role('ROLE_USER')")
 *
 */
class CartController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
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

        return $this->render('default/index.html.twig', array('count'=>$count));
    }
    /**
     * @Route("/cart", name="view_cart")
     */
    public function showAction()
    {

        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $user_cart = $this->getDoctrine()
            ->getRepository('AppBundle:Cart')
            ->findBy(['user' => $user]);
        if ( $user_cart )
        {
            $user_products = $this->getDoctrine()
                ->getRepository('AppBundle:Shipping')
                ->findBy( array('cart' => $user_cart[0]->getId()) );
            $arrQuantities = [];



            foreach ($user_products as $item) {

                $arrQuantities[] = $item->getProduct()->getId();
            }
            $arr = array_count_values($arrQuantities);
            foreach ($arr as $key=>$value) {

            $prices = $this->getDoctrine()
                ->getRepository('AppBundle:Product')
                ->findBy( array('id' => $key ) );
            $arr[$key] = ['count' => $value, 'price' => $prices[0]->getPrice()];
                $idProd = $this->getDoctrine()
                    ->getRepository('AppBundle:Product')
                    ->findBy( array('id' => $key ) );
                $arr[$key] = ['count' => $value, 'price' => $prices[0]->getPrice(), 'id'=>$prices[0]->getId()];

            }
            //echo '<pre>'; var_dump($arr);
            /*foreach ($prices as $item) {
                echo '<pre>'; var_dump($item->getName());
            }*/
//die();



            return $this->render('cart/show.html.twig', array(
                'products'  => $user_products,
                'cart_data' => $user_cart[0],
                'arr' => $arr
            ));
        } else {
            return $this->render('cart/show.html.twig', array(
                'products'  => null,
                'cart_data' => null

            ));
        }
        //return new Response(''. $user_products[0]->getProduct()->getPrice() );
        return $this->render('cart/show.html.twig');
    }

    /**
     * @Route("/cart/addTo/{productId}", name="add_to_cart")
     * @param $productId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addAction($productId)
    {

        $securityContext = $this->container->get('security.authorization_checker');
        if ( $securityContext->isGranted('IS_AUTHENTICATED_FULLY') )
        {
            $em = $this->getDoctrine()->getManager();
            $user = $this->container->get('security.token_storage')->getToken()->getUser();
            $product = $this->getDoctrine()
                ->getRepository('AppBundle:Product')
                ->find($productId);
            $exsit_cart = $this->getDoctrine()
                ->getRepository('AppBundle:Cart')
                ->findBy(['user' => $user]);
            if ( !$exsit_cart )
            {
                $cart = new Cart();
                $cart->setUser($user);

                $cart->setTotalPrice($product->getPrice());
                $cart->setQuantity(1);
                $em->persist($cart);


                $em->flush();
                $ship = new Shipping();
                $ship->setQuantity(1);
                $ship->setProduct($product);
                $ship->setCart($cart);
                $em->persist($ship);
                $em->flush();
            }
            else
            {
                $cart = $exsit_cart[0];


                $cart->setTotalPrice($cart->getTotalPrice() + $product->getPrice());
                $em->persist($cart);
                $em->flush();
                $ship = new Shipping();
                $ship->setQuantity(1);
                $ship->setProduct($product);
                $ship->setCart($cart);
                $em->persist($ship);
                $em->flush();
            }
            //return new Response('user id  '.$product->getId());
            return $this->redirect($this->generateUrl('products_index'));
        }
        else
        {
            return $this->redirect($this->generateUrl('login'));
        }
    }

    /**
     * @Route("/cart/remove/{itemProduct}/{itemCart}", name="remove_item")
     * @param $itemProduct
     * @param $itemCart
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeActione($itemProduct, $itemCart)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:Shipping');
        $ship = $repository->findOneBy(array('product' => $itemProduct, 'cart' => $itemCart));
        //echo '<pre>'; var_dump($ship);die();
        //$final_price = $ship->getCart()->getTotalPrice() - ($ship->getProduct()->getPrice() * $ship->getQuantity());
        //$ship->getCart()->setTotalPrice($final_price);
        if ($ship) $em->remove($ship);
        $em->flush();
        return $this->redirect($this->generateUrl('view_cart'));
    }
    /**
     * @Route("/cart/edit/{itemProduct}/{itemCart}", name="edit item")
     */
    public function editActione(Request $request, $itemProduct, $itemCart)
    {
        if ( $request->getMethod() === 'POST' )
        {
            $new_quantity =$request->request->get('quantity');
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository('AppBundle:Shipping');
            $ship = $repository->findOneBy(array('product' => $itemProduct, 'cart' => $itemCart));
            if( $ship->getQuantity() < $new_quantity )
            {
                $ship->setQuantity($new_quantity);
                $final_price = $ship->getCart()->getTotalPrice() + $ship->getProduct()->getPrice();
                $ship->getCart()->setTotalPrice($final_price);
            }
            elseif( $ship->getQuantity() > $new_quantity )
            {
                $ship->setQuantity($new_quantity);
                $final_price = $ship->getCart()->getTotalPrice() - $ship->getProduct()->getPrice();
                $ship->getCart()->setTotalPrice($final_price);
            }
            $em->flush();
        }
        //return new Response(''. $new_quantity );
        return $this->redirect($this->generateUrl('view_cart'));
    }
    /**
     * @Route("/cart/clear/{cart}", name="clear_cart")
     */
    public function clearActione($cart)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:Shipping');
        $ship = $repository->findBy(array('cart' => $cart));
        foreach ($ship as $one_prod)
        {
            $em->remove($one_prod);
            $em->flush();
        }
        $cart_repository = $em->getRepository('AppBundle:Cart');
        $one_cart = $cart_repository->findOne(['id' => $cart]);
        $em->remove($one_cart);
        $em->flush();
        return $this->redirect($this->generateUrl('view_cart'));
    }

    /**
     * @Route("/checkout", name="user_cart_checkout")
     *
     */
    public function checkoutCart()
    {

        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $userFunds = $user->getCash();
        $cartTotal = $this->getDoctrine()
            ->getRepository('AppBundle:Cart')
            ->findBy(['user' => $user]);
        $total = $cartTotal[0]->getTotalPrice();

        $userCart = $this->getDoctrine()
            ->getRepository('AppBundle:Cart')
            ->findBy(['user' => $user]);

            $userProducts = $this->getDoctrine()
                ->getRepository('AppBundle:Shipping')
                ->findBy( array('cart' => $userCart[0]->getId()) );

            /*if ($total > $userFunds) {
                $this->session->getFlashBag()
                    ->add("danger", "You do not have enough funds in your account to complete the order");

                return false;
            }*/


        $productsPlainText = [];
        foreach ($userProducts as $product) {
            $productId = $product->getProduct()->getId();
            $availableQuantity = $this->getDoctrine()->getRepository(Product::class)->findOneBy(['id'=>$productId])->getQuantity();
            //echo '<pre>'; var_dump($availableQuantity);die();
            if ($availableQuantity < 1) {
                $this->addFlash("danger", "Not enough stocks for some of the products in your cart");
                return $this->redirectToRoute("view_cart");


            }

            if ($userFunds < $total) {
                $this->addFlash("dangercash", "Not enough cash in your account.");
                return $this->redirectToRoute("view_cart");


            }
                $product->setQuantity(0);
            $productPrice = $product->getProduct()->getPrice();
            $user->setCash($user->getCash() - $productPrice);
            $sellerId = $product->getProduct()->getUser()->getId();
            $seller = $this->getDoctrine()
                ->getRepository('AppBundle:User')
                ->findBy(['id' => $sellerId])[0];
            $role = $seller->getRoles();
            if ($role=='ROLE_USER') $seller->setCash($seller->getCash() + $productPrice);
            $productForSeller = $this->getDoctrine()
                ->getRepository('AppBundle:Product')
                ->findBy(['name'=>$product->getProduct()->getName(),
                    'description'=>$product->getProduct()->getDescription(),
                    'user'=>$sellerId])[0];
            $productForSeller->setQuantity($productForSeller->getQuantity() - 1);

            $productsForUser = $this->getDoctrine()
                ->getRepository('AppBundle:Product')
                ->findBy(['name'=>$product->getProduct()->getName(), 'description'=>$product->getProduct()->getDescription(), 'user'=>$user->getId()]);




            if(count($productsForUser) > 0) {
                $productsForUser[0]->setQuantity($productsForUser[0]->getQuantity() + 1);
                $em->persist($productsForUser[0]);
                $em->flush();
            } else {
                $newProduct = new Product();
                $newProduct->setName($product->getProduct()->getName());
                $newProduct->setDescription($product->getProduct()->getDescription());

                $newProduct->setImageFile($product->getProduct()->getImageFile());
                $newProduct->setQuantity(1);
                $newProduct->setPrice($product->getProduct()->getPrice());
                $newProduct->setCategory($product->getProduct()->getCategory());
                $newProduct->setUser($user);

                $newProduct->setOwner('user');
                $cart = $this->getDoctrine()
                    ->getRepository('AppBundle:Cart')
                    ->findBy(['user' => $user]);

                $em->persist($user);
                $em->persist($cartTotal[0]);

                $em->persist($newProduct);




                $em->flush();
            }
            //$entity = $em->getRepository('AppBundle:Cart')->findBy(['id' => $cart[0]])[0]->getId();

            //$entityObj = $em->getRepository('AppBundle:Cart')->find($entity);

            //$em->remove($entityObj);
        $this->removeActione($productId, $userCart);

            $em->persist($user);

            //$em->persist($newProduct);
            $cartTotal[0]->setTotalPrice(0);
            //$em->persist($cartTotal);


            $em->flush();
        }




        //return true;

        return $this->redirectToRoute("view_cart");
    }
}