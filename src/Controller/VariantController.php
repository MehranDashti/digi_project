<?php

namespace App\Controller;

use App\Entity\Variant;
use App\Form\UpdateVariantType;
use App\Form\VariantType;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;

class VariantController extends AbstractController
{
    /**
     * @Route("/variant/list", name="variant_list")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function list(): Response
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
    public function add(Request $request): Response
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
                echo "<pre>";
                print_r($e->getMessage());
                die();
                $entityManager->getConnection()->rollBack();
                $this->addFlash("error", "There is some problem you can not create variant :(:(");
            }
        }

        return $this->render('variant/add.html.twig', [
            'form' => $form->createView(),
            'errors' => $form->getErrors()
        ]);
    }

    /**
     * @param $variant_id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @Route("/variant/edit/{variant_id}", name="edit_variant", requirements={"variant_id"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @author Mehran
     */
    public function edit($variant_id, Request $request): Response
    {
        $variant = $this->getDoctrine()
            ->getRepository(Variant::class)
            ->findOneBy(['id' => $variant_id]);

        $entityManager = $this->getDoctrine()->getManager();
        $form = $this->createForm(UpdateVariantType::class, $variant);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->getConnection()->beginTransaction();
            try {
                $entityManager->persist($variant);
                $entityManager->flush();
                $entityManager->getConnection()->commit();

                $this->addFlash("success", "New Variant has been updated successfully :);)");
                return $this->redirectToRoute('variant_list');
            } catch (Exception $e) {
                $entityManager->getConnection()->rollBack();
                $this->addFlash("error", "There is some problem you can not create Variant :(:(");
            }
        }

        return $this->render('variant/update.html.twig', [
            'form' => $form->createView(),
            'errors' => $form->getErrors()
        ]);
    }

    /**
     * @param $variant_id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @Route("/variant/delete/{variant_id}", name="delete_variant", requirements={"variant_id"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @author Mehran
     */
    public function delete($variant_id): Response
    {
        $variant = $this->getDoctrine()
            ->getRepository(Variant::class)
            ->findOneBy(['id' => $variant_id]);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->getConnection()->beginTransaction();
        try {
            $entityManager->remove($variant);
            $entityManager->flush();
            $entityManager->getConnection()->commit();

            $this->addFlash("success", "Variant has been deleted successfully :);)");
            return $this->redirectToRoute('variant_list');
        } catch (Exception $e) {
            $entityManager->getConnection()->rollBack();
            $this->addFlash("error", "There is some problem you can not delete Variant :(:(");
        }
    }
}
