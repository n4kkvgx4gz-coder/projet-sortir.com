<?php

namespace App\Controller;

use App\Entity\Inscription;
use App\Entity\Sortie;
use App\Entity\Utilisateur;
use App\Form\SortieType;
use App\Repository\InscriptionRepository;
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

    #[Route('/sortie/{id}/inscription', name: 'sortie_inscription', methods: ['POST'])]
    public function inscription(
        Sortie $sortie,
        EntityManagerInterface $entityManager,
        InscriptionRepository $inscriptionRepository
    ): Response {
        /** @var Utilisateur|null $user */
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour vous inscrire.');
            return $this->redirectToRoute('app_login');
        }

        if (!$sortie->isEtat()) {
            $this->addFlash('danger', 'Cette sortie n’est pas ouverte aux inscriptions.');
            return $this->redirectToRoute('app_inscription', [
                'id' => $sortie->getId(),
            ]);
        }

        if ($sortie->getDateCloture() < new \DateTime()) {
            $this->addFlash('danger', 'La date limite d’inscription est dépassée.');

            return $this->redirectToRoute('app_inscription', [
                'id' => $sortie->getId(),
            ]);
        }

        if ($sortie->getInscriptions()->count() >= $sortie->getNbInscriptionsMax()) {
            $this->addFlash('danger', 'Il n’y a plus de places disponibles.');

            return $this->redirectToRoute('app_inscription', [
                'id' => $sortie->getId(),
            ]);
        }

        $dejaInscrit = $inscriptionRepository->findOneBy([
            'sortie' => $sortie,
            'participant' => $user,
        ]);

        if ($dejaInscrit) {
            $this->addFlash('warning', 'Vous êtes déjà inscrit à cette sortie.');

            return $this->redirectToRoute('app_inscription', [
                'id' => $sortie->getId(),
            ]);
        }

        $inscription = new Inscription();
        $inscription->setSortie($sortie);
        $inscription->setParticipant($user);
        $inscription->setDateInscription(new \DateTime());

        $entityManager->persist($inscription);
        $entityManager->flush();

        $this->addFlash('success', 'Vous êtes bien inscrit à la sortie.');

        return $this->redirectToRoute('app_inscription', [
            'id' => $sortie->getId(),
        ]);
    }

    #[Route('/inscription/{id}', name: 'app_inscription', methods: ['GET'])]
    public function inscriptionPage(Sortie $sortie): Response
    {
        return $this->render('inscription/inscription.html.twig', [
            'sortie' => $sortie,
        ]);
    }
}
