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
        # Get object from doctrine manager
        $em = $this->getDoctrine()->getManager();
        # Get logged user then get his ['id']
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        /** Check IF user have exist cart  **/
        # select cart from database where user id equal to cureent logged user using [ findByUser() ]
        $user_cart = $this->getDoctrine()
            ->getRepository('AppBundle:Cart')
            ->findBy(['user' => $user]);
        if ( $user_cart )
        {
            # Then select all user cart products to display it to user
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



            # pass selected products to the twig page to show them
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
        # pass selected products to the twig page to show them
        return $this->render('cart/show.html.twig');
    }

    /**
     * @Route("/cart/addTo/{productId}", name="add_to_cart")
     * @param $productId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addAction($productId)
    {
        # First of all check if user logged in or not by using FOSUSERBUNDLE
        #    authorization_checker
        # if user logged in so add the selected product to his cart and redirect user to products page
        # else redirect user to login page to login first or create a new account
        $securityContext = $this->container->get('security.authorization_checker');
        # If user logged in
        if ( $securityContext->isGranted('IS_AUTHENTICATED_FULLY') )
        {
            # Get object from doctrine manager
            $em = $this->getDoctrine()->getManager();
            # Get logged user then get his ['id']
            $user = $this->container->get('security.token_storage')->getToken()->getUser();
            # for any case wewill need to select product so select it first
            # select specific product which have passed id using ['find(passedID)']
            $product = $this->getDoctrine()
                ->getRepository('AppBundle:Product')
                ->find($productId);
            /** Check IF user have exist cart  **/
            # select cart from database where user id equal to cureent logged user using [ findByUser() ]
            $exsit_cart = $this->getDoctrine()
                ->getRepository('AppBundle:Cart')
                ->findBy(['user' => $user]);
            # if there's no cart to this user create a new one
            if ( !$exsit_cart )
            {
                # defince cart object
                $cart = new Cart();
                # set user whose own this cart
                $cart->setUser($user);

                # set initail total price for cart which equal to product price
                $cart->setTotalPrice($product->getPrice());
                $cart->setQuantity(1);
                # persist all cart data to can use it in create shipping object
                $em->persist($cart);


                # flush it
                $em->flush();
                # create shipping object
                $ship = new Shipping();
                # set all its data quantity initail equal to 1 and passed product and cart created
                $ship->setQuantity(1);
                $ship->setProduct($product);
                $ship->setCart($cart);
                # persist it and flush doctrine to save it
                $em->persist($ship);
                $em->flush();
            }
            # if user have one so just add new item price to cart price and add it to shipping
            else
            {
                # Get cart from retrived object
                $cart = $exsit_cart[0];


                # set initail total price for cart which equal to product price
                $cart->setTotalPrice($cart->getTotalPrice() + $product->getPrice());
                # persist all cart data to can use it in create shipping object
                $em->persist($cart);
                # flush it
                $em->flush();
                # create shipping object
                $ship = new Shipping();
                # set all its data quantity initail equal to 1 and passed product and cart created
                $ship->setQuantity(1);
                $ship->setProduct($product);
                $ship->setCart($cart);
                # persist it and flush doctrine to save it
                $em->persist($ship);
                $em->flush();
            }
            //return new Response('user id  '.$product->getId());
            return $this->redirect($this->generateUrl('products_index'));
        }
        # if user not logged in yet
        else
        {
            # go to adding product form
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
        # get an object from doctrine db and get Shipping Entity to work on it
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:Shipping');
        # select wanted item from shipping table to delete it
        $ship = $repository->findOneBy(array('product' => $itemProduct, 'cart' => $itemCart));
        //echo '<pre>'; var_dump($ship);die();
        # Calculate the new total price for cart by subtract deleted item price from total one
        //$final_price = $ship->getCart()->getTotalPrice() - ($ship->getProduct()->getPrice() * $ship->getQuantity());
        # update the total price of cart
        //$ship->getCart()->setTotalPrice($final_price);
        # Remove item from db
        if ($ship) $em->remove($ship);
        $em->flush();
        return $this->redirect($this->generateUrl('view_cart'));
    }
    /**
     * @Route("/cart/edit/{itemProduct}/{itemCart}", name="edit item")
     */
    public function editActione(Request $request, $itemProduct, $itemCart)
    {
        # in the start check if user edit field and click on button
        if ( $request->getMethod() === 'POST' )
        {
            # read data from quantity field
            $new_quantity =$request->request->get('quantity');
            # get oject from doctrine manager to mange operation
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository('AppBundle:Shipping');
            # select wanted item from shipping table to edit it
            $ship = $repository->findOneBy(array('product' => $itemProduct, 'cart' => $itemCart));
            # check if new quantity less than old one so subtract total price
            # otherwise, add to it
            if( $ship->getQuantity() < $new_quantity )
            {
                # edit selected item quantity
                $ship->setQuantity($new_quantity);
                # Calculate the new total price for cart by sum added item price to total one
                $final_price = $ship->getCart()->getTotalPrice() + $ship->getProduct()->getPrice();
                # update the total price of cart
                $ship->getCart()->setTotalPrice($final_price);
            }
            elseif( $ship->getQuantity() > $new_quantity )
            {
                # edit selected item quantity
                $ship->setQuantity($new_quantity);
                # Calculate the new total price for cart by sum added item price to total one
                $final_price = $ship->getCart()->getTotalPrice() - $ship->getProduct()->getPrice();
                # update the total price of cart
                $ship->getCart()->setTotalPrice($final_price);
            }
            # flush operations to update database
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
        # get an object from doctrine db and get Shipping Entity to work on it
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:Shipping');
        # select wanted item from shipping table to delete it
        $ship = $repository->findBy(array('cart' => $cart));
        # Fetch all them using foeach loop and delete them
        foreach ($ship as $one_prod)
        {
            # Remove item from db
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

            # Then select all user cart products to display it to user
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