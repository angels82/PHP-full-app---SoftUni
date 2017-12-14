<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Product;
use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    /**
     * @Route("/register")
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
     */
    public function viewProfileAction()
    {
        $user = $this->getUser();
        return $this->render('user/profile_view.html.twig',['user' => $user]);
    }

    /**
     * @Route("/profile/edit", name="profile_edit")
     * @param Request $request
     * @return Response
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
