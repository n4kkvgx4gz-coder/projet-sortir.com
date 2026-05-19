<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SortieController extends AbstractController
{
    #[Route('/sortie', name: 'app_sortie')]
    public function index(): Response
    {
        return $this->render('sortie/index.html.twig');
    }

    #[Route('/sorties/create', name: 'sortie_create')]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $sortie = new Sortie();

        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('app_sortie');
        }

        return $this->render('sortie/create.html.twig', [
            'sortieForm' => $form,
        ]);
    }
}
