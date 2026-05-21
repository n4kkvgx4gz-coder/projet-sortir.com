<?php

namespace App\Controller;

use App\Entity\Inscription;
use App\Entity\Sortie;
use App\Entity\Utilisateur;
use App\Form\SortieType;
use App\Repository\InscriptionRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SortieController extends AbstractController
{
    #[Route('/sortie', name: 'app_sortie')]
    public function index(
        Request $request,
        SortieRepository $sortieRepository,
        CategorieRepository $categorieRepository
    ): Response {
        $q = $request->query->get('q');
        $dateMin = $request->query->get('dateMin');
        $dateMax = $request->query->get('dateMax');
        $departement = $request->query->get('departement');
        $categorieId = $request->query->get('categorie');

        $sorties = $sortieRepository->findWithFilters(
            $q,
            $dateMin,
            $dateMax,
            $departement,
            $categorieId
        );

        return $this->render('sortie/index.html.twig', [
            'sorties' => $sorties,
            'categories' => $categorieRepository->findAll(),
            'q' => $q,
            'dateMin' => $dateMin,
            'dateMax' => $dateMax,
            'departement' => $departement,
            'categorieId' => $categorieId,
        ]);
    }

    #[Route('/sorties/create', name: 'sortie_create', methods: ['GET', 'POST'])]
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

    // detail de l'inscription
    /** * si la date de début de la sortie est plus ancienne que aujourd’hui - 1 mois, Symfony redirige et empêche la consultation de la sortie. * * Exemple : aujourd’hui 20/05/2026, une sortie du 10/04/2026 ne sera plus consultable. * */
    #[Route('/sortie/{id}', name: 'sortie_detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function detail(
        Sortie $sortie,
        InscriptionRepository $inscriptionRepository
    ): Response {
        $dateLimiteConsultation = (new \DateTime())->modify('-1 month');

        if ($sortie->getDateDebut() < $dateLimiteConsultation) {
            $this->addFlash('danger', 'Cette sortie n’est plus consultable.');

            return $this->redirectToRoute('app_sortie');
        }

        /** @var Utilisateur|null $user */
        $user = $this->getUser();

        $dejaInscrit = null;

        if ($user) {
            $dejaInscrit = $inscriptionRepository->findOneBy([
                'sortie' => $sortie,
                'participant' => $user,
            ]);
        }

        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,
            'dejaInscrit' => $dejaInscrit,
        ]);
    }

    #[Route('/sortie/{id}/inscription', name: 'sortie_inscription', requirements: ['id' => '\d+'], methods: ['POST'])]
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

            return $this->redirectToRoute('sortie_detail', [
                'id' => $sortie->getId(),
            ]);
        }

        if ($sortie->getDateCloture() < new \DateTime()) {
            $this->addFlash('danger', 'Les inscriptions sont clôturées pour cette sortie.');

            return $this->redirectToRoute('sortie_detail', [
                'id' => $sortie->getId(),
            ]);
        }

        if ($sortie->getInscriptions()->count() >= $sortie->getNbInscriptionsMax()) {
            $this->addFlash('danger', 'Il n’y a plus de places disponibles.');

            return $this->redirectToRoute('sortie_detail', [
                'id' => $sortie->getId(),
            ]);
        }

        $dejaInscrit = $inscriptionRepository->findOneBy([
            'sortie' => $sortie,
            'participant' => $user,
        ]);

        if ($dejaInscrit) {
            $this->addFlash('warning', 'Vous êtes déjà inscrit à cette sortie.');

            return $this->redirectToRoute('sortie_detail', [
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

        return $this->redirectToRoute('sortie_detail', [
            'id' => $sortie->getId(),
        ]);
    }

    #[Route('/sortie/{id}/desistement', name: 'sortie_desistement', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function desistement(
        Sortie $sortie,
        EntityManagerInterface $entityManager,
        InscriptionRepository $inscriptionRepository
    ): Response {
        /** @var Utilisateur|null $user */
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté.');

            return $this->redirectToRoute('app_login');
        }

        if ($sortie->getDateDebut() <= new \DateTime()) {
            $this->addFlash('danger', 'Vous ne pouvez plus vous désister, la sortie a déjà débuté.');

            return $this->redirectToRoute('sortie_detail', [
                'id' => $sortie->getId(),
            ]);
        }

        $inscription = $inscriptionRepository->findOneBy([
            'sortie' => $sortie,
            'participant' => $user,
        ]);

        if (!$inscription) {
            $this->addFlash('warning', 'Vous n’êtes pas inscrit à cette sortie.');

            return $this->redirectToRoute('sortie_detail', [
                'id' => $sortie->getId(),
            ]);
        }

        $entityManager->remove($inscription);
        $entityManager->flush();

        $this->addFlash('success', 'Votre désistement a bien été pris en compte.');

        return $this->redirectToRoute('sortie_detail', [
            'id' => $sortie->getId(),
        ]);
    }

}
