<?php

namespace App\Repository;

use App\Data\FiltreSortie;
use App\Entity\Sortie;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 *
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function add(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

   /**
//     * @return Sortie[] Returns an array of Sortie objects
//     */
   public function listeSortiesMoinsUnMois($date)
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->andWhere('s.dateHeureDebut >= :val');
        $queryBuilder->setParameter('val', $date);
        $queryBuilder->orderBy('s.dateHeureDebut', 'ASC');
        $query = $queryBuilder->getQuery();
        $query->getResult();

        $paginator = new Paginator($query);

        return $paginator;
    }

    public function findAvecFiltres(FiltreSortie $filtres)
    {
        $queryBuilder = $this->createQueryBuilder('s');

        if (!empty($filtres->campus))
        {
            $queryBuilder->andWhere('s.campus IN (:c)')
                         ->setParameter('c', $filtres->campus);
        }

        if (!empty($filtres->q))
        {
            $queryBuilder->andWhere('s.nom LIKE :q')
                         ->setParameter('q', "%{$filtres->q}%");
        }

        if (!empty($filtres->dateMin))
        {
            $queryBuilder->andWhere('s.dateHeureDebut >= :min')
                ->setParameter('min', $filtres->dateMin);
        }

        if (!empty($filtres->dateMax))
        {
            $queryBuilder->andWhere('s.dateHeureDebut <= :max')
                ->setParameter('max', $filtres->dateMax);
        }

        //TODO : rajouter les filtres à cocher

        $query = $queryBuilder->getQuery();
        $query->getResult();


        $paginator = new Paginator($query);

        return $paginator;
    }
}
