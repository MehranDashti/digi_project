<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("IS_AUTHENTICATED_FULLY")
 */
class ProductController extends AbstractController
{
    /**
     * @Route("/product/list", name="product_list")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function list()
    {
        $products = $this->getDoctrine()
            ->getRepository(Product::class)
            ->findAll();

        return $this->render('product/list.html.twig', [
            'products' => $products,
        ]);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @Route("/product/add", name="add_product")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function add(Request $request)
    {
        $product = new Product();
        $entityManager = $this->getDoctrine()->getManager();
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->getConnection()->beginTransaction();
            try {
//                $product->setCreatedBy($this->getUser());
//                $product->setUpdatedBy($this->getUser());
                $entityManager->persist($product);
                $entityManager->flush();
                $entityManager->getConnection()->commit();

                $this->addFlash("success", "New Product has been created successfully :);)");
                return $this->redirectToRoute('product_list');
            } catch (Exception $e) {
                $entityManager->getConnection()->rollBack();
                echo "<pre>";
                print_r($e->getMessage());
                die();
                $this->addFlash("error", "There is some problem you can not create Product :(:(");
                return $this->redirectToRoute('add_product');
            }
        }

        return $this->render('product/add.html.twig', [
            'form' => $form->createView(),
            'errors' => $form->getErrors()
        ]);
    }
}
