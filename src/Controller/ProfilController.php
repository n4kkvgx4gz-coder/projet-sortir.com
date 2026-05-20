<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\GererProfilType;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
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
#[Route('/modifier', name: 'modifier', requirements: ['id'=>'\d+'], methods: ['GET', 'POST'])]
#[IsGranted('ROLE_USER')]
public function gererProfil(Request $request) : Response {
    $user = $this->getUser();
    $userId = $user->getId();
    $profilForm = $this-> createForm(GererProfilType::class, $user);
    $profilForm->handleRequest($request);
    return $this->redirectToRoute('profil_detail', ['id' => $userId]);
}
}
