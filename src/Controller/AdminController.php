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
     * Importe les comptes depuis un CSV UTF-8 avec en-têtes.
     * Le fichier est entièrement vérifié avant toute insertion (pas d'import partiel).
     * Colonnes : nom, prenom, pseudo, email, telephone, campus, roles, actif, password.
     * campus : identifiant numérique ou nom exact du campus existant.
     * roles : ROLE_USER ou ROLE_ADMIN ; password : mot de passe temporaire en clair,
     * haché avant stockage. Ne jamais importer une empreinte de mot de passe.
     */
    #[Route('/gestion-utilisateur/creerUtilisateursCSV', name: 'creer-utilisateurs-CSV', methods: ['POST'])]
    public function creerUserCSV(
        Request $request,
        EntityManagerInterface $entityManager,
        CampusRepository $campusRepository,
        UtilisateurRepository $utilisateurRepository,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        if (!$this->isCsrfTokenValid('admin_import_csv', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $upload = $request->files->get('file');
        if (!$upload || !$upload->isValid() || $upload->getSize() > 2 * 1024 * 1024) {
            $this->addFlash('danger', 'Choisissez un fichier CSV valide de 2 Mo maximum.');
            return $this->redirectToRoute('admin_gestion-utilisateur');
        }

        $handle = fopen($upload->getPathname(), 'rb');
        if ($handle === false) {
            $this->addFlash('danger', 'Impossible de lire le fichier CSV.');
            return $this->redirectToRoute('admin_gestion-utilisateur');
        }

        try {
            $firstLine = fgets($handle);
            if ($firstLine === false) {
                throw new \\RuntimeException('Le fichier CSV est vide.');
            }
            $firstLine = preg_replace('/^\\xEF\\xBB\\xBF/', '', $firstLine);
            $separator = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
            rewind($handle);
            $headers = fgetcsv($handle, 0, $separator, '"', '');
            if (!$headers) {
                throw new \\RuntimeException('En-têtes CSV manquants.');
            }
            $headers[0] = preg_replace('/^\\xEF\\xBB\\xBF/', '', $headers[0]);
            $headers = array_map(static fn ($value) => strtolower(trim((string) $value)), $headers);
            $required = ['nom', 'prenom', 'pseudo', 'email', 'campus', 'password'];
            if (count(array_diff($required, $headers)) || count($headers) !== count(array_unique($headers))) {
                throw new \\RuntimeException('En-têtes CSV invalides : nom, prenom, pseudo, email, campus et password sont obligatoires.');
            }

            $campuses = $campusRepository->findAll();
            $pending = [];
            $emails = [];
            $pseudos = [];
            $line = 1;
            while (($values = fgetcsv($handle, 0, $separator, '"', '')) !== false) {
                ++$line;
                if (count($values) === 1 && trim((string) $values[0]) === '') {
                    continue;
                }
                if ($line > 1001 || count($values) !== count($headers)) {
                    throw new \\RuntimeException('Ligne ' . $line . ' : format incorrect ou limite de 1000 comptes dépassée.');
                }
                $data = array_combine($headers, array_map('trim', $values));
                foreach ($required as $field) {
                    if ($data[$field] === '') {
                        throw new \\RuntimeException('Ligne ' . $line . ' : champ obligatoire manquant (' . $field . ').');
                    }
                }
                $email = strtolower($data['email']);
                $pseudo = $data['pseudo'];
                if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 180 ||
                    mb_strlen($pseudo) < 3 || mb_strlen($pseudo) > 30 ||
                    mb_strlen($data['nom']) > 30 || mb_strlen($data['prenom']) > 30 ||
                    mb_strlen($data['telephone'] ?? '') > 15 ||
                    strlen($data['password']) < 8) {
                    throw new \\RuntimeException('Ligne ' . $line . ' : email, longueur de champ ou mot de passe invalide (8 caractères minimum).');
                }
                if (isset($emails[$email]) || isset($pseudos[$pseudo]) ||
                    $utilisateurRepository->findOneBy(['email' => $email]) ||
                    $utilisateurRepository->findOneBy(['pseudo' => $pseudo])) {
                    throw new \\RuntimeException('Ligne ' . $line . ' : email ou pseudo déjà utilisé.');
                }
                $emails[$email] = true;
                $pseudos[$pseudo] = true;
                $campus = null;
                foreach ($campuses as $candidate) {
                    if ((string) $candidate->getId() === $data['campus'] ||
                        mb_strtolower((string) $candidate->getNomCampus()) === mb_strtolower($data['campus'])) {
                        $campus = $candidate;
                        break;
                    }
                }
                if (!$campus) {
                    throw new \\RuntimeException('Ligne ' . $line . ' : campus inconnu.');
                }
                $role = strtoupper($data['roles'] ?? 'ROLE_USER');
                if (!in_array($role, ['ROLE_USER', 'ROLE_ADMIN'], true)) {
                    throw new \\RuntimeException('Ligne ' . $line . ' : rôle non reconnu.');
                }
                $actif = strtolower($data['actif'] ?? '1');
                if (!in_array($actif, ['1', '0', 'true', 'false', 'oui', 'non'], true)) {
                    throw new \\RuntimeException('Ligne ' . $line . ' : statut actif invalide.');
                }
                $user = new Utilisateur();
                $user->setNom($data['nom']);
                $user->setPrenom($data['prenom']);
                $user->setPseudo($pseudo);
                $user->setEmail($email);
                $user->setTelephone(($data['telephone'] ?? '') !== '' ? $data['telephone'] : null);
                $user->setCampus($campus);
                $user->setRoles([$role]);
                $user->setAdministrateur($role === 'ROLE_ADMIN');
                $user->setActif(in_array($actif, ['1', 'true', 'oui'], true));
                $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
                $user->setUrlPhoto('default.png');
                $pending[] = $user;
            }
            if (!$pending) {
                throw new \\RuntimeException('Aucun utilisateur à importer.');
            }
            foreach ($pending as $user) {
                $entityManager->persist($user);
            }
            $entityManager->flush();
            $this->addFlash('success', count($pending) . ' utilisateur(s) importé(s).');
        } catch (\\Throwable $exception) {
            $this->addFlash('danger', 'Import CSV refusé : ' . $exception->getMessage());
        } finally {
            fclose($handle);
        }
        return $this->redirectToRoute('admin_gestion-utilisateur');
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
        Request $request,
        UtilisateurRepository $utilisateurRepository,
        SortieRepository $sortieRepository,
        InscriptionRepository $inscriptionRepository,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('admin_delete_user_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }
        if ($this->getUser()?->getId() === $id) {
            $this->addFlash('danger', 'Vous ne pouvez pas supprimer votre propre compte administrateur.');
            return $this->redirectToRoute('admin_gestion-utilisateur');
        }
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
    #[Route('/gestion-utilisateur/desactiver/{id}', name: 'gestion-utilisateur-desactiver', methods: ['POST'])]
    public function desactiverUSer(
        int $id,
        Request $request,
        UtilisateurRepository $utilisateurRepository,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('admin_toggle_user_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }
        if ($this->getUser()?->getId() === $id) {
            $this->addFlash('danger', 'Vous ne pouvez pas désactiver votre propre compte administrateur.');
            return $this->redirectToRoute('admin_gestion-utilisateur');
        }
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
