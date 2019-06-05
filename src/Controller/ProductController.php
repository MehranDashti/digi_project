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
     * @author Mehran
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

    /**
     * @param $product_id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @Route("/product/edit/{product_id}", name="edit_product", requirements={"product_id"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @author Mehran
     */
    public function edit($product_id, Request $request)
    {
        $product = $this->getDoctrine()
            ->getRepository(Product::class)
            ->findOneBy(['id' => $product_id]);

        $entityManager = $this->getDoctrine()->getManager();
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->getConnection()->beginTransaction();
            try {
                $entityManager->persist($product);
                $entityManager->flush();
                $entityManager->getConnection()->commit();

                $this->addFlash("success", "New Product has been updated successfully :);)");
                return $this->redirectToRoute('product_list');
            } catch (Exception $e) {
                $entityManager->getConnection()->rollBack();
                $this->addFlash("error", "There is some problem you can not create Product :(:(");
                return $this->redirectToRoute('edit_product');
            }
        }

        return $this->render('product/update.html.twig', [
            'form' => $form->createView(),
            'errors' => $form->getErrors()
        ]);
    }

    /**
     * @param $product_id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @Route("/product/delete/{product_id}", name="delete_product", requirements={"product_id"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @author Mehran
     */
    public function delete($product_id)
    {
        $product = $this->getDoctrine()
            ->getRepository(Product::class)
            ->findOneBy(['id' => $product_id]);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->getConnection()->beginTransaction();
        try {
            $entityManager->remove($product);
            $entityManager->flush();
            $entityManager->getConnection()->commit();

            $this->addFlash("success", "Product has been deleted successfully :);)");
            return $this->redirectToRoute('product_list');
        } catch (Exception $e) {
            $entityManager->getConnection()->rollBack();
            $this->addFlash("error", "There is some problem you can not delete Product :(:(");
            return $this->redirectToRoute('delete_product');
        }
    }
}
