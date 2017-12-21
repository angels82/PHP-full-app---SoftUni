<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Product;
use AppBundle\Entity\Role;
use AppBundle\Entity\Roles;
use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    /**
     * @Route("/register", name="register")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function registerAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() and $form->isValid()) {
            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->getPassword());
            $user->setPassword($password);
            $roleRep = $this->getDoctrine()->getRepository(Role::class);
            $userRole = $roleRep->findOneBy(['name'=>'ROLE_USER']);
            $user->addRole($userRole);
            $em=$this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();


            return $this->redirectToRoute('security_login');
        }
        return $this->render('register/register.html.twig', ['form'=>$form->createView()]);
    }

    /**
     * @Route("/profile", name="profile_view")
     * @return Response
     * @Security("has_role('ROLE_USER')")
     */
    public function viewProfileAction()
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
        $user = $this->getUser();
        return $this->render('user/profile_view.html.twig',['user' => $user]);
    }

    /**
     * @Route("/profile/edit", name="profile_edit")
     * @param Request $request
     * @return Response
     * @Security("has_role('ROLE_USER')")
     */
    public function profileEditAction(Request $request){

        $em = $this->getDoctrine()->getManager();

        //$repo = $em->getRepository(User::class);
        $user = $this->getUser();

        $form = $this->createForm(UserType::class, $user);
        $form->remove('password');

        //$form->add('submit', SubmitType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('profile_view');
        }

        return $this->render('user/profile_edit.html.twig',
            ['form'=>$form->createView()]
        );
    }
    /**
     * @Route("/profile/change_password", name="change_password")
     * @param Request $request
     * @return Response
     * @Security("has_role('ROLE_USER')")
     */
    public function changePasswordAction(Request $request){

        $em = $this->getDoctrine()->getManager();

        //$repo = $em->getRepository(User::class);
        $user = $this->getUser();

        $form = $this->createForm(UserType::class, $user);
        $form->remove('username');
        $form->remove('name');
        $form->remove('email');
        $form->remove('address');
        $form->remove('city');

        //$form->add('submit', SubmitType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('profile_view');
        }

        return $this->render('user/chane_password.html.twig',
            ['form'=>$form->createView()]
        );
    }
    /**
     * @Route("/profile/personal_products", name="personal_products")
     * @return Response
     * @Security("has_role('ROLE_USER')")
     */
    public function viewPersonalProductsAction(){
        $repo = $this->getDoctrine()->getRepository(User::class);
        $user = $this->getUser();
        $repoProduct = $this->getDoctrine()->getRepository(Product::class);
        $products = $repoProduct->findBy(
            ['user' => $user]);
        if($products === null){
            throw $this->createNotFoundException('No products found for the current user');
        }
        return $this->render('categories/viewUserProducts.html.twig', ['products'=>$products]);
    }
}
