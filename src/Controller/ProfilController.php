<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\GererProfilType;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
public function gererProfil(Request $request, EntityManagerInterface $entityManager) : Response {
    $user = $this->getUser();
    $profilForm = $this-> createForm(GererProfilType::class, $user,['action' => $this->generateUrl('profil_modifier'),'method' => 'POST']);
    $profilForm->handleRequest($request);

//    return $this->redirectToRoute('profil_detail', ['id' => $userId]);
    if ($profilForm->isSubmitted() && $profilForm->isValid()) {
        try {

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', "Le souhait a bien été modifié.");

            return $this->redirectToRoute('profil_detail');
        } catch (Exception $exception) {
            $this->addFlash('danger', $exception->getMessage());
        }
    }
    return $this->render('user/gererProfil.html.twig', ["user"=> $user, "GererProfilType"=> $profilForm]);
}

    #[Route('/supprimer', name: 'supprimer', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function SupprimerProfil(Request $request) : Response {
        $userId = $this->getUser()->getId();

        return $this->redirectToRoute('profil_detail', ['id' => $userId]);
    }

    // Pour récupérer l'utilisateur avec son id pour voir profil participant
    #[Route('/profil/{id}', name: 'profil_detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function detail(Utilisateur $utilisateur): Response
    {
        return $this->render('user/voirProfil.html.twig', [
            'utilisateur' => $utilisateur,
        ]);
    }

}
