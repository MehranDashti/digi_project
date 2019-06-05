<?php

namespace App\Controller;

use App\Entity\Variant;
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
}
