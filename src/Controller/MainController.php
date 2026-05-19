<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\SortieRepository;
final class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(SortieRepository $sortieRepository): Response
    {
        $sorties = $sortieRepository->findBy(
            ['etat' => true],
            ['dateDebut' => 'ASC'],
            6
        );

        return $this->render('main/index.html.twig', [
            'sorties' => $sorties,
        ]);
    }
}
