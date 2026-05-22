<?php

namespace App\Controller;

use App\Form\GestionUtilisateurType;
use App\Repository\SortieRepository;
use App\Repository\UtilisateurRepository;
use Cassandra\Type\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin', name: 'admin_')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/gestion-utilisateur', name: 'gestion-utilisateur', methods: ['GET'])]
    public function allUSer(UtilisateurRepository $utilisateurRepository): Response
    {
        $utilisateurs = $utilisateurRepository->findAll();


        return $this->render('user/gestionUtilisateur.html.twig', [
            'utilisateurs' => $utilisateurs
        ]);
    }

    #[Route('/gestion-utilisateur/supprimer/{id}', name: 'gestion-utilisateur-supprimer', methods: ['GET'])]
    public function supprimerUSer(int $id, UtilisateurRepository $utilisateurRepository, EntityManagerInterface $entityManager): Response
    {
        try{
            $utilisateur = $utilisateurRepository->find($id);

            if (!(null === $utilisateur)) {
                $entityManager->remove($utilisateur);
                $entityManager->flush();
                $this->addFlash('success', "L'utilisateur a bien été supprimé.");
            } else {
                $this->addFlash('danger', "Le l'utilisateur n'existe pas.");
            }
        } catch (Exception $exception) {
            $this->addFlash('danger', $exception->getMessage());
        }

        return $this->redirectToRoute('admin_gestion-utilisateur', [

        ]);
    }

    #[Route('/gestion-utilisateur/modifier/{id}', name: 'gestion-utilisateur-modifier', methods: ['GET'])]
    public function modifierUSer(int $id, UtilisateurRepository $utilisateurRepository, EntityManagerInterface $entityManager): Response
    {
        try{
            $utilisateur = $utilisateurRepository->find($id);
            $form = $this -> createForm(GestionUtilisateurType::class, $utilisateur);

            if ($form->isSubmitted() && $form->isValid()) {
                try {

                    $entityManager->persist($utilisateur);
                    $entityManager->flush();

                    $this->addFlash('success', "L'utilisateur a bien été modifié.");

                    return $this->redirectToRoute('admin_gestion-utilisateur');
                } catch (Exception $exception) {
                    $this->addFlash('danger', $exception->getMessage());
                }
            }
        } catch (Exception $exception) {
            $this->addFlash('danger', $exception->getMessage());
        }

        return $this->redirectToRoute('admin_gestion-utilisateur', [

        ]);
    }

    #[Route('/gestion-utilisateur/desactiver/{id}', name: 'gestion-utilisateur-desactiver', methods: ['GET'])]
    public function desactiverUSer(int $id, UtilisateurRepository $utilisateurRepository, EntityManagerInterface $entityManager): Response
    {
        try{
            $utilisateur = $utilisateurRepository->find($id);

            if (!(null === $utilisateur)) {
                $utilisateur->setActif(false);
                $entityManager->flush();
                $this->addFlash('success', "L'utilisateur a bien été désactivé.");
            } else {
                $this->addFlash('danger', "Le l'utilisateur n'a pas été désactivé.");
            }
        } catch (Exception $exception) {
            $this->addFlash('danger', $exception->getMessage());
        }

        return $this->redirectToRoute('admin_gestion-utilisateur', [

        ]);
    }




    #[Route('/gestion-sortie', name: 'gestion-sortie', methods: ['GET'])]
    public function allSorties(SortieRepository $sortieRepository): Response
    {
        $sorties = $sortieRepository->findAll();


        return $this->render('user/gestionUtilisateur.html.twig', [
            'sorties' => $sorties
        ]);
    }
}
