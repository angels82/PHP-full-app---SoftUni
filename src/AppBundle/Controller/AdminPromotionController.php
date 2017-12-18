<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Product;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Promotion;
use AppBundle\Form\AddEditPromotionForm;
use AppBundle\Form\CategoryPromotionsForm;
use AppBundle\Form\PromotionForm;
use Symfony\Component\HttpFoundation\Session\Session;


/**
 * Class AdminPromotionController
 * @package AppBundle\Controller
 *
 * @Security("has_role('ROLE_ADMIN')")
 *
 *
 */
class AdminPromotionController extends Controller
{



    public function setPromotionToCategory(Promotion $promotion, Category $category)
    {
        /** @var ArrayCollection|Product[] $products */
        $products = $category->getProducts();
        foreach ($products as $product) {
            if ($product->getPromotions()->contains($promotion)) {
                continue;
            }

            $product->setPromotion($promotion);
        }

        $this->getDoctrine()->getManager()->persist($category);
        $this->getDoctrine()->getManager()->flush();
    }
        /*$this->session
            ->getFlashBag()
            ->add("success",
                "All products of category '{$category->getName()}' was promoted with '{$promotion->getName()}' promotion");
    }*/

    public function setPromotionToProducts(Promotion $promotion)
    {
        $allProducts = $this->getDoctrine()->getRepository(Product::class)
            ->findAll();

        foreach ($allProducts as $product) {
            /*if ($product->getPromotions()->contains($promotion)) {
                continue;
            }*/

            $product->setPromotion($promotion);
        }

        $this->getDoctrine()->getManager()->persist($promotion);
        $this->getDoctrine()->getManager()->flush();

        /*$this->session
            ->getFlashBag()
            ->add("success", "All products was promoted with '{$promotion->getName()}' promotion");*/
    }

    public function unsetPromotionToProducts(Promotion $promotion)
    {
        $allProducts = $this->getDoctrine()->getRepository(Product::class)
            ->findAll();

        foreach ($allProducts as $product) {
            /*if (!$product->getPromotions()->contains($promotion)) {
                continue;
            }*/

            $product->unsetPromotion($promotion);
        }

        $this->getDoctrine()->getManager()->persist($promotion);
        $this->getDoctrine()->getManager()->flush();

        /*$this->session
            ->getFlashBag()
            ->add("success", "'{$promotion->getName()}' promotion was removed from all products!");*/
    }


    /**
     * @Route("admin/listPromotions", name="admin_list_promotions")
     *
     * @param Request $request
     * @return Response
     */


    public function listPromotionsAction(Request $request)
    {

        $promotions = $this->getDoctrine()->getRepository(Promotion::class)
                ->findByQueryBuilder();

        return $this->render("admin/promotions/list.html.twig", [
            "promotions" => $promotions
        ]);
    }

    /**
     * @Route("/promotions/delete/{id}", name="admin_delete_promotion")
     * @Method("POST")
     *
     * @param Request $request
     * @param Promotion $promotion
     * @return Response
     */
    public function deletePromotionAction(Request $request, Promotion $promotion)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($promotion);
        $em->flush();

        return $this->redirectToRoute("admin_list_promotions");
    }

    /**
     * @Route("/add", name="admin_add_promotion")
     *
     * @param Request $request
     * @return Response
     */
    public function addPromotionAction(Request $request)
    {
        $form = $this->createForm(AddEditPromotionForm::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Promotion $promotion */
            $promotion = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($promotion);
            $em->flush();

            return $this->redirectToRoute("admin_list_promotions");
        }

        return $this->render("admin/promotions/add.html.twig", [
            "add_form" => $form->createView()
        ]);
    }

    /**
     * @Route("/edit/{id}", name="admin_edit_promotion")
     *
     * @param Request $request
     * @param Promotion $promotion
     * @return Response
     */
    public function editPromotionAction(Request $request, Promotion $promotion)
    {
        $form = $this->createForm(AddEditPromotionForm::class, $promotion);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Promotion $promotion */
            $promotion = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($promotion);
            $em->flush();

            return $this->redirectToRoute("admin_list_promotions");
        }

        return $this->render("admin/promotions/edit.html.twig", [
            "edit_form" => $form->createView()
        ]);
    }



    /**
     * @Route("/categoryPromotion", name="admin_add_promotion_to_category")
     *
     * @param Request $request
     * @return Response
     */
    public function addPromotionToCategoryAction(Request $request)
    {
        $form = $this->createForm(CategoryPromotionsForm::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $promotion = $form->get("promotion")->getData();
            $category = $form->get("category")->getData();

            //$promoService = $this->container->get("promotions");
            $this->setPromotionToCategory($promotion, $category);

            return $this->redirectToRoute("admin_list_promotions");
        }

        return $this->render("admin/promotions/add_category.html.twig", [
            "add_form" => $form->createView()
        ]);
    }

    /**
     * @Route("/productsPromotion", name="admin_add_promotion_to_all_products")
     *
     * @param Request $request
     * @return Response
     */
    public function addPromotionToAllProductsAction(Request $request)
    {
        $form = $this->createForm(PromotionForm::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $promotion = $form->get("promotion")->getData();

            //$promoService = $this->get("service.promotions_service");
            $this->setPromotionToProducts($promotion);

            return $this->redirectToRoute("admin_list_promotions");
        }

        return $this->render("admin/promotions/add_all.html.twig", [
            "form_name" => "Add promotion to all Products",
            "add_form" => $form->createView()
        ]);
    }

    /**
     * @Route("/products/removePromotion", name="admin_remove_promotion_to_all_products")
     *
     * @param Request $request
     * @return Response
     */
    public function removePromotionFromAllProductsAction(Request $request)
    {
        $form = $this->createForm(PromotionForm::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $promotion = $form->get("promotion")->getData();

            //$promoService = $this->get('promotions_service');
            $this->unsetPromotionToProducts($promotion);

            return $this->redirectToRoute("admin_list_promotions");
        }

        return $this->render("admin/promotions/add_all.html.twig", [
            "form_name" => "Remove promotion from all Products",
            "add_form" => $form->createView()
        ]);
    }
}
