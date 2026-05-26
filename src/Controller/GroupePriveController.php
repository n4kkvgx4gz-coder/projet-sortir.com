<?php

namespace App\Controller;

    use App\Entity\GroupePrive;
    use App\Repository\GroupePriveRepository;
    use App\Repository\UtilisateurRepository;
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Attribute\Route;
    use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/groupes-prives', name: 'groupe_prive_')]
#[IsGranted('ROLE_USER')]
class GroupePriveController extends AbstractController
{
    #[Route('', name: 'liste', methods: ['GET'])]
    public function liste(GroupePriveRepository $groupePriveRepository): Response
    {
        $groupes = $groupePriveRepository->findBy([
            'createur' => $this->getUser()
        ]);

        return $this->render('groupe_prive/liste.html.twig', [
            'groupes' => $groupes,
        ]);
    }

    #[Route('/creer', name: 'creer', methods: ['GET', 'POST'])]
    public function creer(
        Request $request,
        UtilisateurRepository $utilisateurRepository,
        EntityManagerInterface $em
    ): Response {
        $utilisateurs = $utilisateurRepository->findAll();

        if ($request->isMethod('POST')) {
            $groupe = new GroupePrive();
            $groupe->setNom($request->request->get('nom'));
            $groupe->setCreateur($this->getUser());

            $participantsIds = $request->request->all('participants');

            foreach ($participantsIds as $id) {
                $participant = $utilisateurRepository->find($id);

                if ($participant) {
                    $groupe->addParticipant($participant);
                }
            }

            $em->persist($groupe);
            $em->flush();

            $this->addFlash('success', 'Groupe privé créé avec succès.');

            return $this->redirectToRoute('groupe_prive_liste');
        }

        return $this->render('groupe_prive/creer.html.twig', [
            'utilisateurs' => $utilisateurs,
        ]);
    }

    #[Route('/{id}', name: 'detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function detail(GroupePrive $groupe): Response
    {
        if ($groupe->getCreateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('groupe_prive/detail_groupe.html.twig', [
            'groupe' => $groupe,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'supprimer', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function supprimer(GroupePrive $groupe, EntityManagerInterface $em): Response
    {
        if ($groupe->getCreateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $em->remove($groupe);
        $em->flush();

        $this->addFlash('success', 'Groupe supprimé.');

        return $this->redirectToRoute('groupe_prive_liste');
    }
}
