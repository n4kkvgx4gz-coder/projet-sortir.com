<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\GestionUtilisateurType;
use App\Repository\CampusRepository;
use App\Repository\SortieRepository;
use App\Repository\InscriptionRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use SplFileObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/admin', name: 'admin_')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/gestion-utilisateur', name: 'gestion-utilisateur', methods: ['GET'])]
    public function allUSer(UtilisateurRepository $utilisateurRepository): Response
    {
        $utilisateurs = $utilisateurRepository->findAll();

        return $this->render('user/gestionUtilisateur.html.twig', [
            'utilisateurs' => $utilisateurs,
        ]);
    }

    // Ajouter un utilisateur manuellement
    #[Route('/gestion-utilisateur/creerUtilisateur', name: 'creer_utilisateur', methods: ['GET', 'POST'])]
    public function creerUtilisateur(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        UtilisateurRepository $utilisateurRepository,
        CampusRepository $campusRepository
    ): Response {
        $campusList = $campusRepository->findAll();

        if ($request->isMethod('POST')) {
            $email = trim($request->request->get('email'));
            $pseudo = trim($request->request->get('pseudo'));

            if ($utilisateurRepository->findOneBy(['email' => $email])) {
                $this->addFlash('danger', 'Cet email existe déjà.');
                return $this->redirectToRoute('admin_creer_utilisateur');
            }

            if ($utilisateurRepository->findOneBy(['pseudo' => $pseudo])) {
                $this->addFlash('danger', 'Ce pseudo existe déjà.');
                return $this->redirectToRoute('admin_creer_utilisateur');
            }

            $utilisateur = new Utilisateur();

            $utilisateur->setNom($request->request->get('nom'));
            $utilisateur->setPrenom($request->request->get('prenom'));
            $utilisateur->setPseudo($pseudo);
            $utilisateur->setEmail($email);
            $utilisateur->setTelephone($request->request->get('telephone'));
            $role = $request->request->get('role', 'ROLE_USER');
            $utilisateur->setRoles([$role]);
            if ($role === 'ROLE_ADMIN') {
                $utilisateur->setAdministrateur(true);
            } else {
                $utilisateur->setAdministrateur(false);
            }

            $campusId = $request->request->get('campus');

            if ($campusId) {
                $campus = $campusRepository->find($campusId);

                if ($campus) {
                    $utilisateur->setCampus($campus);
                }
            }

            $utilisateur->setActif($request->request->getBoolean('actif'));

            $role = $request->request->get('role', 'ROLE_USER');
            $utilisateur->setRoles([$role]);

            $password = $request->request->get('password');
            $hashedPassword = $passwordHasher->hashPassword($utilisateur, $password);
            $utilisateur->setPassword($hashedPassword);

            $utilisateur->setUrlPhoto('default.png');

            $em->persist($utilisateur);
            $em->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès.');

            return $this->redirectToRoute('admin_gestion-utilisateur');
        }

        return $this->render('user/creer_utilisateur.html.twig', [
            'campusList' => $campusList,
        ]);
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route("/gestion-utilisateur/creerUtilisateursCSV", name: 'creer-utilisateurs-CSV', methods: ['GET', 'POST'])]
    public function creerUserCSV(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager){
        if ($request->isMethod('POST')) {

            $csvfile = $request->files->get('file');

            $file = new SplFileObject($csvfile);
            $file->setFlags(SplFileObject::READ_CSV);

            $idx = 0;
            $columns = [];

            foreach ($file as $fields) {
                // get properties' names
                if (++$idx === 1) {
                    /** @var array<int|string> $columns */
                    $columns = $fields;

                    continue;
                }

                /** @var array<string, string> $fields */
                if (!(\count(array_filter($fields)) > 0)) { // ignore last empty line
                    continue;
                }

                $entityArray = array_combine($columns, $fields);
                // prepare data before denormalization (this could be done in a custom denormalizer)
                $entityArray['active'] = (bool) $entityArray['active'];


                $entity = $this->serializer->denormalize($entityArray, Utilisateur::class);

                $user = new Utilisateur();
                $user->setNom($entityArray['nom']);
                $user->setPrenom($entityArray['prenom']);
                $user->setPseudo($entityArray['pseudo']);
                $user->setEmail($entityArray['email']);
                $user->setTelephone($entityArray['telephone']);
                $user->setRoles($entityArray['roles']);
                $user->setCampus($entityArray['campus']);
                $user->setActif($entityArray['actif']);
                $entityManager->persist($user);
                $entityManager->flush();
            }


        }



    }

    // Modifier un utilisateur par un administrateur
    #[Route('/gestion-utilisateur/modifier/{id}', name: 'gestion-utilisateur-modifier', methods: ['GET', 'POST'])]
    public function modifierUSer(
        int $id,
        Request $request,
        UtilisateurRepository $utilisateurRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $utilisateur = $utilisateurRepository->find($id);

        if (!$utilisateur) {
            $this->addFlash('danger', "L'utilisateur n'existe pas.");
            return $this->redirectToRoute('admin_gestion-utilisateur');
        }

        $anciennePhoto = $utilisateur->getUrlPhoto();

        $form = $this->createForm(GestionUtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $file = $form->get('url_photo')->getData();

                if ($file) {
                    $extension = $file->guessExtension() ?: 'bin';
                    $newFileName = uniqid('profil_', true) . '.' . $extension;

                    try {
                        $file->move('images/', $newFileName);
                        $utilisateur->setUrlPhoto($newFileName);
                    } catch (FileException $e) {
                        $this->addFlash('danger', "Erreur lors de l'envoi de l'image.");
                        return $this->redirectToRoute('admin_gestion-utilisateur-modifier', [
                            'id' => $utilisateur->getId(),
                        ]);
                    }
                } else {
                    $utilisateur->setUrlPhoto($anciennePhoto);
                }

                if ($form->has('roles')) {
                    $role = $form->get('roles')->getData();

                    $utilisateur->setRoles([$role]);
                    $utilisateur->setAdministrateur($role === 'ROLE_ADMIN');
                }

                $entityManager->flush();

                $this->addFlash('success', "L'utilisateur a bien été modifié.");

                return $this->redirectToRoute('admin_gestion-utilisateur');
            } catch (Exception $exception) {
                $this->addFlash('danger', $exception->getMessage());
            }
        }

        return $this->render('user/modifierUtilisateur.html.twig', [
            'form' => $form,
            'utilisateur' => $utilisateur,
        ]);
    }

    // Supprimer un utilisateur par un administrateur
    #[Route('/gestion-utilisateur/supprimer/{id}', name: 'gestion-utilisateur-supprimer', methods: ['POST'])]
    public function supprimerUSer(
        int $id,
        UtilisateurRepository $utilisateurRepository,
        SortieRepository $sortieRepository,
        InscriptionRepository $inscriptionRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $utilisateur = $utilisateurRepository->find($id);

        if (!$utilisateur) {
            $this->addFlash('danger', "L'utilisateur n'existe pas.");
            return $this->redirectToRoute('admin_gestion-utilisateur');
        }
        $inscriptions = $inscriptionRepository->findBy([
            'participant' => $utilisateur,
        ]);

        foreach ($inscriptions as $inscription) {
            $entityManager->remove($inscription);
        }

        $sortiesOrganisees = $sortieRepository->findBy([
            'organisateur' => $utilisateur,
        ]);

        foreach ($sortiesOrganisees as $sortie) {
            $entityManager->remove($sortie);
        }

        $entityManager->remove($utilisateur);
        $entityManager->flush();

        $this->addFlash(
            'success',
            "L'utilisateur et ses sorties organisées ont bien été supprimés."
        );

        return $this->redirectToRoute('admin_gestion-utilisateur');
    }

    //Désactiver un utilisateur
    #[Route('/gestion-utilisateur/desactiver/{id}', name: 'gestion-utilisateur-desactiver', methods: ['GET'])]
    public function desactiverUSer(
        int $id,
        UtilisateurRepository $utilisateurRepository,
        EntityManagerInterface $entityManager
    ): Response {
        try {
            $utilisateur = $utilisateurRepository->find($id);

            if ($utilisateur !== null) {
                if ($utilisateur->isActif()) {
                    $utilisateur->setActif(false);
                    $this->addFlash('success', "L'utilisateur a bien été désactivé.");
                } else {
                    $utilisateur->setActif(true);
                    $this->addFlash('success', "L'utilisateur a bien été activé.");
                }

                $entityManager->flush();
            } else {
                $this->addFlash('danger', "L'utilisateur n'existe pas.");
            }
        } catch (Exception $exception) {
            $this->addFlash('danger', $exception->getMessage());
        }

        return $this->redirectToRoute('admin_gestion-utilisateur');
    }

    // Gestion de sortie par l'administrateur
    #[Route('/gestion-sortie', name: 'gestion-sortie', methods: ['GET'])]
    public function allSorties(SortieRepository $sortieRepository): Response
    {
        $sorties = $sortieRepository->findAll();

        return $this->render('sortie/gestionSortie.html.twig', [
            'sorties' => $sorties,
        ]);
    }
}
