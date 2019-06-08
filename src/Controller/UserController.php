<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Variant;
use App\Form\SearchIndexType;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user")
     */
    public function index(Request $request)
    {
        $variant = new Product();
        $entityManager = $this->getDoctrine()->getManager();
        $form = $this->createForm(SearchIndexType::class, $variant);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->getConnection()->beginTransaction();
            try {
                $entityManager->persist($variant);
                $entityManager->flush();
                $entityManager->getConnection()->commit();

                $this->addFlash("success", "New Variant has been created successfully :);)");
                return $this->redirectToRoute('variant_list');
            } catch (Exception $e) {
                $entityManager->getConnection()->rollBack();
                $this->addFlash("error", "There is some problem you can not create variant :(:(");
                return $this->redirectToRoute($request->getRequestUri());
            }
        }

        return $this->render('user/index.html.twig', [
            'form' => $form->createView(),
            'errors' => $form->getErrors()
        ]);
    }
}
