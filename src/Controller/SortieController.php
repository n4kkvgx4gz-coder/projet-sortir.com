<?php

namespace App\Controller;

use App\Entity\Inscription;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Utilisateur;
use App\Entity\Ville;
use App\Form\SortieType;
use App\Repository\CategorieRepository;
use App\Repository\InscriptionRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;



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

        $organisateur = $request->query->get('organisateur');
        $inscrit = $request->query->get('inscrit');
        $disponible = $request->query->get('disponible');
        $passees = $request->query->get('passees');

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

            'organisateur' => $organisateur,
            'inscrit' => $inscrit,
            'disponible' => $disponible,
            'passees' => $passees,
        ]);
    }



    #[IsGranted('ROLE_USER')]
    #[Route('/sorties/create', name: 'sortie_create', methods: ['GET', 'POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        VilleRepository $villeRepository
    ): Response {

        $sortie = new Sortie();

        $sortie->setEtat(true);
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour créer une sortie.');

            return $this->redirectToRoute('app_login');
        }

        $sortie->setOrganisateur($user);

        $sortie = new Sortie();
        $sortie->setEtat(true);
        $sortie->setOrganisateur($this->getUser());
        $form = $this->createForm(SortieType::class, $sortie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $nomVille = $form->get('ville')->getData();
            $codePostal = $form->get('codePostal')->getData();

            $ville = $villeRepository->findOneBy([
                'nom_ville' => $nomVille,
                'code_postal' => $codePostal,
            ]);

            if (!$ville) {

                $ville = new Ville();

                $ville->setNomVille($nomVille);
                $ville->setCodePostal($codePostal);

                $entityManager->persist($ville);
            }

            $lieu = new Lieu();

            $lieu->setNomLieu(
                $form->get('nomLieu')->getData()
            );

            $lieu->setRue(
                $form->get('rue')->getData()
            );

            $lieu->setVille($ville);

            $entityManager->persist($lieu);

            $sortie->setLieu($lieu);

            $file = $form->get('image')->getData();

            if ($file) {

                $extension = $file->guessExtension();

                if (!$extension) {
                    $extension = 'bin';
                }

                $newFileName =
                    rand(1, 99999) . '.' . $extension;

                try {

                    $file->move(
                        'images/',
                        $newFileName
                    );

                    $sortie->setUrlPhoto($newFileName);

                } catch (FileException $e) {

                    $this->addFlash(
                        'danger',
                        $e->getMessage()
                    );
                }
            }

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

        if (
            $sortie->getInscriptions()->count()
            >= $sortie->getNbInscriptionsMax()
        ) {

            $this->addFlash(
                'danger',
                'Il n’y a plus de places.'
            );

            return $this->redirectToRoute(
                'sortie_detail',
                ['id' => $sortie->getId()]
            );
        }

        $dejaInscrit = $inscriptionRepository->findOneBy([
            'sortie' => $sortie,
            'participant' => $user,
        ]);

        if ($dejaInscrit) {
            $this->addFlash('warning', 'Vous êtes déjà inscrit à cette sortie.');

            $this->addFlash(
                'warning',
                'Vous êtes déjà inscrit.'
            );

            return $this->redirectToRoute(
                'sortie_detail',
                ['id' => $sortie->getId()]
            );
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
    ): Response
    {
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

    #[Route('/sortie/{id}/annuler', name: 'sortie_annuler', methods: ['GET', 'POST'])]
    public function annuler(
        Sortie                 $sortie,
        Request                $request,
        EntityManagerInterface $em
    ): Response
    {
        $user = $this->getUser();

        if (
            $sortie->getOrganisateur() !== $user
            && !$user->isAdministrateur()
        ) {
            throw $this->createAccessDeniedException(
                "Vous ne pouvez pas annuler cette sortie."
            );
        }

        if ($sortie->getDateDebut() <= new \DateTime()) {
            $this->addFlash('danger', "Impossible d'annuler une sortie déjà commencée.");
            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }

        if ($request->isMethod('POST')) {
            $motif = $request->request->get('motifAnnulation');

            if (empty($motif)) {
                $this->addFlash('danger', "Le motif d'annulation est obligatoire.");
            } else {
                $sortie->setEtat(0);
                $sortie->setMotifAnnulation($motif);

                $em->flush();

                $this->addFlash('success', "La sortie a bien été annulée.");
                return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
            }
        }

        return $this->render('sortie/annuler.html.twig', [
            'sortie' => $sortie,
        ]);
    }
}

