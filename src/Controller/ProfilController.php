<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\GererProfilType;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/profil', name: 'profil_')]
class ProfilController extends AbstractController
{
    #[Route('', name: 'detail', methods: ['GET'])]
    public function list(UtilisateurRepository $utilisateurRepository): Response
    {
        $utilisateur = $this->getUser();

        return $this->render('user/voirProfil.html.twig', [
            'utilisateur' => $utilisateur
        ]);
    }
#[Route('/modifier', name: 'modifier', methods: ['GET', 'POST'])]
#[IsGranted('ROLE_USER')]
public function gererProfil(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator) : Response {
    $user = $this->getUser();
    $profilForm = $this-> createForm(GererProfilType::class, $user,['action' => $this->generateUrl('profil_modifier'),'method' => 'POST']);
    $profilForm->handleRequest($request);

//    return $this->redirectToRoute('profil_detail', ['id' => $userId]);
    if ($profilForm->isSubmitted() && $profilForm->isValid()) {
        try {
            $directory = 'images/';
            $file = $profilForm['url_photo']->getData();

            if($file){
                $extension = $file->guessExtension();

                if (!$extension) {
                    // extension cannot be guessed
                    $extension = 'bin';
                }
                try{
                    $newFileName = rand(1, 99999).'.'.$extension;
                    $file->move($directory, $newFileName);
                    $user->setUrlPhoto($newFileName);
                }catch (FileException $e){
                    dump($e->getMessage());
                }

            }
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', "Le profil a bien été modifié.");

            return $this->redirectToRoute('profil_detail');
        } catch (Exception $exception) {
            $this->addFlash('danger', $exception->getMessage());
        }
    }
    return $this->render('user/gererProfil.html.twig', ["user"=> $user, "form"=> $profilForm]);
}

    #[Route('/supprimer', name: 'supprimer', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function SupprimerProfil(UtilisateurRepository $Repository,
                                    EntityManagerInterface $entityManager) : Response {
        $userId = $this->getUser()->getId();


        return $this->redirectToRoute('profil_detail', ['id' => $userId]);
    }

    // Pour récupérer l'utilisateur avec son id pour voir profil participant
    #[Route('/{id}', name: 'voir', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function detail(int $id, UtilisateurRepository $utilisateurRepository): Response
    {
        $utilisateur = $utilisateurRepository->find($id);

        if (!$utilisateur) {
            throw $this->createNotFoundException('Utilisateur introuvable');
        }

        return $this->render('user/voirProfil.html.twig', [
            'utilisateur' => $utilisateur,
        ]);
    }

    #[Route('/gestion-utilisateur', name: 'gestion-utilisateur', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function allUSer(UtilisateurRepository $utilisateurRepository): Response
    {
        $utilisateurs = $utilisateurRepository->findAll();


        return $this->render('user/gestionUtilisateur.html.twig', [
            'utilisateurs' => $utilisateurs
        ]);
    }

}
