<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\SearchIndexType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractController
{
    /**
     * @param Request $request
     * @Route("/user", name="user")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request): Response
    {
        $product = new Product();
        /**
         * This section has been used for prepare the embeded form for search product and variant
         */
        $form = $this->createForm(SearchIndexType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $products = $this->getDoctrine()
                ->getRepository(Product::class)
                ->fetchProduct($product);

        } else {
            $products = $this->getDoctrine()
                ->getRepository(Product::class)
                ->fetchProduct();
        }

        return $this->render('user/index.html.twig', [
            'form' => $form->createView(),
            'errors' => $form->getErrors(),
            'products' => $products
        ]);
    }

    /**
     * @param $product_id
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/user/product-info/{product_id}", name="product_info", requirements={"product_id"="\d+"})
     */
    public function userInfo($product_id): Response
    {
        $product = $this->getDoctrine()
            ->getRepository(Product::class)
            ->findOneBy(['id' => $product_id]);

        return $this->render('user/productInfo.html.twig', [
            'product' => $product
        ]);
    }
}
