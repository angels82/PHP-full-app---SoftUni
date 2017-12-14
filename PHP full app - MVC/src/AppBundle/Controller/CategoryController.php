<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Product;
use AppBundle\Form\CategoryType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends Controller
{
    /**
     * @Route("/categories/create", name="categories_create")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function categoryCreateAction(Request $request){

        $category = new Category();

        $form = $this->createForm(CategoryType::class, $category);
        $form->add('submit', SubmitType::class);


        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();

            return $this->redirectToRoute('category_view');
        }

        return $this->render('categories/create.html.twig',
            ['form'=>$form->createView()]
        );
    }

    /**
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("/categories/delete/{id}", name="category_delete", requirements={"id": "\d+"})
     */
    public function deleteCategoryAction($id)
    {


        $category = $this->getDoctrine()->getRepository(Category::class)->find($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($category);
        $em->flush();

        return $this->redirectToRoute('category_view');
    }

    /**
     * @Route("/categories", name="category_view")
     * @return Response
     */
    public function categoryViewAction(){
        $repo = $this->getDoctrine()->getRepository(Category::class);
        $category = $repo->findAll();
        if($category === null){
            throw $this->createNotFoundException('No product found');
        }
        return $this->render('categories/index.html.twig', ['category'=>$category]);
    }

    /**
     * @Route("/categories/{id}", name="category_view_all_products", requirements={"id": "\d+"})
     * @param int $id
     * @return Response
     */
    public function categoryViewWithProductsAction(int $id){
        $repo = $this->getDoctrine()->getRepository(Category::class);
        $category = $repo->find($id);
        $repoProduct = $this->getDoctrine()->getRepository(Product::class);
        $products = $repoProduct->findBy(
            ['category' => $id]);
        if($products === null){
            throw $this->createNotFoundException('No products found in this category');
        }
        return $this->render('categories/viewProductsInCategory.html.twig', ['products'=>$products]);
    }
}
