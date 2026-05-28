<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    // ex: Campus Quimper → département 29 → sorties dans les villes dont le code postal commence par 29
    public function findWithFilters(
        ?string $q,
        ?string $dateMin,
        ?string $dateMax,
        ?string $departement = null,
        ?string $categorieId = null,
        $user = null,
        $organisateur = null,
        $inscrit = null,
        $disponible = null,
        $passees = null
    ): array {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.lieu', 'l')
            ->leftJoin('l.ville', 'v')
            ->leftJoin('s.categorie', 'c')
            ->addSelect('l', 'v', 'c')
            ->andWhere('s.etat = 1')
            ->orderBy('s.dateDebut', 'ASC');

        if (!empty($q)) {
            $qb->andWhere(
                's.nom LIKE :q
            OR s.description LIKE :q
            OR l.nom_lieu LIKE :q
            OR v.nom_ville LIKE :q'
            )
                ->setParameter('q', '%' . $q . '%');
        }

        if (!empty($dateMin)) {
            $qb->andWhere('s.dateDebut >= :dateMin')
                ->setParameter('dateMin', new \DateTime($dateMin));
        }

        if (!empty($dateMax)) {
            $qb->andWhere('s.dateDebut <= :dateMax')
                ->setParameter('dateMax', new \DateTime($dateMax . ' 23:59:59'));
        }

        if (!empty($departement)) {
            $qb->andWhere('v.code_postal LIKE :departement')
                ->setParameter('departement', $departement . '%');
        }

        if (!empty($categorieId)) {
            $qb->andWhere('c.id = :categorieId')
                ->setParameter('categorieId', $categorieId);
        }

        if ($organisateur && $user) {
            $qb->andWhere('s.organisateur = :user')
                ->setParameter('user', $user);
        }

        if ($inscrit && $user) {
            $qb->leftJoin('s.inscriptions', 'i')
                ->andWhere('i.participant = :userInscrit')
                ->setParameter('userInscrit', $user);
        }

        if ($disponible) {
            $qb->andWhere('s.dateCloture >= :today')
                ->andWhere('s.etat = true')
                ->setParameter('today', new \DateTime());
        }

        if ($passees) {
            $qb->andWhere('s.dateDebut < :now')
                ->setParameter('now', new \DateTime());
        }

        return $qb->getQuery()->getResult();
    }
    //    /**
    //     * @return Sortie[] Returns an array of Sortie objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Sortie
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
