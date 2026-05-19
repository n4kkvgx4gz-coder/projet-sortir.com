<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class InscriptionController extends AbstractController
{
    #[Route('/sinscrire/participant', name: 'app_sinscrire_participant')]
    public function index(): Response
    {
        return $this->render('sinscrire_participant/index.html.twig', [
            'controller_name' => 'InscriptionController',
        ]);
    }
}
