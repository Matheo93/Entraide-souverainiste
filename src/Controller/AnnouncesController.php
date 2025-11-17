<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Announces\Announces;
use App\Form\Announces\AnnouncesType;

class AnnouncesController extends AbstractController
{
    #[Route('/creer-une-fiche', name: 'creer-une-fiche')]
    public function index(): Response
    {
        $announce = new Announces();
        $form = $this->createForm(AnnouncesType::class, $announce);

        return $this->render('front/pages/announces/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
