<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Product;
use AppBundle\Entity\User;
use AppBundle\Form\ProductType;
use AppBundle\Form\ProductTypeUpdate;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\File;

class ProductController extends Controller
{
    /**
     * @Route("/products", name="products_index")
     * @return Response
     */
    public function indexAction()
    {
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        //echo '<pre>';var_dump($user);die();

        $repo = $this->getDoctrine()->getRepository(Product::class);
        $products = $repo->findAll();
        return $this->render('products/index.html.twig',['products' => $products, 'user'=>$user]);
    }

    /**
     * @Route("/profile/personal_products", name="personal_products")
     * @return Response
     * @Security("has_role('ROLE_USER')")
     */
    public function viewPersonalProducts()
    {
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        //echo '<pre>';var_dump($user);die();

        $repo = $this->getDoctrine()->getRepository(Product::class);
        $products = $repo->findBy(['user'=>$user]);
        //echo '<pre>';var_dump($products);die();
        return $this->render('products/personal.html.twig',['products' => $products, 'user'=>$user]);
    }

    /**
     * @Route("/products/{id}", name="product_view", requirements={"id": "\d+"})
     * @param int $id
     * @return Response
     */
    public function productViewAction(int $id){
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $repo = $this->getDoctrine()->getRepository(Product::class);
        $product = $repo->find($id);
        if($product === null){
            throw $this->createNotFoundException('No product found');
        }
        return $this->render('products/view.html.twig', ['product'=>$product, 'user'=>$user]);
    }

    /**
     * @Route("/products/create", name="product_create")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @Security("has_role('ROLE_USER')")
     */
    public function productCreateAction(Request $request){

        $product = new Product();
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $product->setUser($user);
        $role=$user->getRoles()[0];
        //echo '<pre>'; var_dump($role) ; die();
        if ($role=='ROLE_USER') {
           $product->setOwner('user') ;
        }
        if ($role=='ROLE_ADMIN') {
            $product->setOwner('shop') ;
        }


        $form = $this->createForm(ProductType::class,$product);
        $form->add('selling', ChoiceType::class, array(
            'choices'  => array(
                'Yes' => 'Yes',
                'No' => 'No',
            )
        ));
        $form->add('submit', SubmitType::class);


        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            /** @var UploadedFile $file */
            $file = $product->getImageFile();

                $filename = md5($product->getName() . random_int(1, 99999)).'.'.$file->guessExtension();

                $file->move(
                    $this->get('kernel')->getRootDir() . '/../web/images/',
                    $filename
                );


                $product->setImageFile($filename);
            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();

            return $this->redirectToRoute('products_index');
        }

        return $this->render('products/create.html.twig',
            ['form'=>$form->createView()]
        );
    }

    /**
     * @Route("/products/update/{id}", name="product_update")
     * @param int $id
     * @param Request $request
     * @return Response
     * @Security("has_role('ROLE_USER')")
     */
    public function productEditAction(int $id, Request $request){

        $em = $this->getDoctrine()->getManager();

        $repo = $em->getRepository(Product::class);
        $product = $repo->find($id);
        $file = $product->getImageFile();
        //echo '<pre>'; var_dump($file); die();
        $form = $this->createForm(ProductTypeUpdate::class, $product, array('method'=>'PATCH'));

        $form->add('submit', SubmitType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            //$file = $product->getImageFile();


            /** @var UploadedFile $file */
            //$file = $product->getImageFile();


            if ($form->get('imageFile')->getData()) {
                $filename = md5($form->get('imageFile')->getData() . random_int(1, 99999)) . '.' . $form->get('imageFile')->getData()->guessExtension();

                $form->get('imageFile')->getData()->move(
                    $this->get('kernel')->getRootDir() . '/../web/images/',
                    $filename
                );
                $product->setImageFile($filename);
            } else {
                $product->setImageFile($file);
            }


            $em->persist($product);
            $em->flush();

            return $this->redirectToRoute('personal');
        }

        return $this->render('products/update.html.twig',
            ['form'=>$form->createView()]
        );
    }
    /**
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("/products/delete/{id}", name="product_delete", requirements={"id": "\d+"})
     * @Security("has_role('ROLE_USER')")
     */
    public function deleteProductAction($id)
    {


        $article = $this->getDoctrine()->getRepository(Product::class)->find($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($article);
        $em->flush();

        return $this->redirectToRoute('personal');
    }

    
}
