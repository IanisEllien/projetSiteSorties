<?php

namespace App\Repository;

use App\Data\FiltreSortie;
use App\Entity\Sortie;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

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

    public function findAvecFiltres(FiltreSortie $filtres, $date, $user)
    {
        $queryBuilder = $this->createQueryBuilder('s');

        $queryBuilder->join('s.organisateur','o');
        $queryBuilder->andWhere('s.dateHeureDebut >= :val');
        $queryBuilder->setParameter('val', $date);

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

        if (!empty($filtres->typeSortie))
        {
            if (in_array('orga',$filtres->typeSortie))
            {
                $queryBuilder->andWhere('o.pseudo = :user')
                    ->setParameter('user',$user->getPseudo());
            }

            if (in_array('inscrit',$filtres->typeSortie))
            {
                $queryBuilder->join('s.participants','p','WITH','p.pseudo = :user')
                //$queryBuilder->andWhere('p.pseudo = :user')
                    ->setParameter('user',$user->getPseudo());
            }

            if (in_array('noninscrit',$filtres->typeSortie))
            {
                $queryBuilder->leftJoin('s.participants','p','WITH','p.pseudo = :user')
                //$queryBuilder->andWhere('p.pseudo <> :user')
                    ->setParameter('user',$user->getPseudo());
            }

            if (in_array('finies', $filtres->typeSortie))
            {
                $dateJour = new DateTime();
                $queryBuilder->andWhere('s.dateLimiteInscription < :dateJour')
                    ->setParameter('dateJour',$dateJour);
            }

        }

        $queryBuilder->orderBy('s.dateHeureDebut', 'ASC');

        $query = $queryBuilder->getQuery();
        $query->getResult();


        $paginator = new Paginator($query);

        return $paginator;
    }
}
