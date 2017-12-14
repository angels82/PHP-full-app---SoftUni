<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Product;
use AppBundle\Form\ProductType;
use AppBundle\Form\ProductTypeUpdate;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProductController extends Controller
{
    /**
     * @Route("/products", name="products_index")
     * @return Response
     */
    public function indexAction()
    {
        $repo = $this->getDoctrine()->getRepository(Product::class);
        $products = $repo->findAll();
        return $this->render('products/index.html.twig',['products' => $products]);
    }

    /**
     * @Route("/products/{id}", name="product_view", requirements={"id": "\d+"})
     * @param int $id
     * @return Response
     */
    public function productViewAction(int $id){
        $repo = $this->getDoctrine()->getRepository(Product::class);
        $product = $repo->find($id);
        if($product === null){
            throw $this->createNotFoundException('No product found');
        }
        return $this->render('products/view.html.twig', ['product'=>$product]);
    }

    /**
     * @Route("/products/create", name="product_create")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function productCreateAction(Request $request){

        $product = new Product();
        //$product->setCreateDate(new \DateTime('now'));

        $form = $this->createForm(ProductType::class,$product);
        $form->add('submit', SubmitType::class);


        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $file = $product->getImageFile();


            /** @var UploadedFile $file */
            $file = $product->getImageFile();

                $filename = md5($product->getName() . random_int(1, 99999)).'.'.$file->guessExtension();

                $file->move(
                    $this->get('kernel')->getRootDir() . '/../web/images/',
                    $filename
                );


                $product->setImageFile($filename);
                //echo '<pre>'; var_dump($file) ; die();
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
     */
    public function productEditAction(int $id, Request $request){

        $em = $this->getDoctrine()->getManager();

        $repo = $em->getRepository(Product::class);
        $product = $repo->find($id);

        $form = $this->createForm(ProductTypeUpdate::class, $product);

        $form->add('submit', SubmitType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $file = $product->getImageFile();


            /** @var UploadedFile $file */
            $file = $product->getImageFile();

            $filename = md5($product->getName() . random_int(1, 99999)).'.'.$file->guessExtension();

            $file->move(
                $this->get('kernel')->getRootDir() . '/../web/images/',
                $filename
            );


            $product->setImageFile($filename);
            $em->persist($product);
            $em->flush();

            return $this->redirectToRoute('products_index');
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
     */
    public function deleteProductAction($id)
    {


        $article = $this->getDoctrine()->getRepository(Product::class)->find($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($article);
        $em->flush();

        return $this->redirectToRoute('products_index');
    }

    
}
