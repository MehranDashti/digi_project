<?php

namespace App\Controller;

use App\Entity\Variant;
use App\Form\VariantType;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class VariantController extends AbstractController
{
    /**
     * @Route("/variant/list", name="variant_list")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function list()
    {
        $variants = $this->getDoctrine()
            ->getRepository(Variant::class)
            ->findAll();

        return $this->render('variant/list.html.twig', [
            'variants' => $variants,
        ]);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @Route("/variant/add", name="add_variant")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @author Mehran
     */
    public function add(Request $request)
    {
        $variant = new Variant();
        $entityManager = $this->getDoctrine()->getManager();
        $form = $this->createForm(VariantType::class, $variant);

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
                return $this->redirectToRoute('add_variant');
            }
        }

        return $this->render('variant/add.html.twig', [
            'form' => $form->createView(),
            'errors' => $form->getErrors()
        ]);
    }
}
