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
        $sorties = $sortieRepository->createQueryBuilder('s')
            ->andWhere('s.etat = :active')
            ->andWhere('s.dateDebut >= :now')
            ->setParameter('active', true)
            ->setParameter('now', new \DateTime())
            ->orderBy('s.dateDebut', 'ASC')
            ->setMaxResults(6)
            ->getQuery()
            ->getResult();

        return $this->render('main/index.html.twig', [
            'sorties' => $sorties,
        ]);
    }
}
