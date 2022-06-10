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

        if (!empty($filtres->typeSortie))
        {

            /*if (in_array('inscrit', $filtres->typeSortie) && in_array('noninscrit', $filtres->typeSortie))
            {
                $queryBuilder = $queryBuilder
                    ->join('s.participants','p');

            }*/

            if (in_array('inscrit',$filtres->typeSortie) && !in_array('noninscrit', $filtres->typeSortie))
            {
                $queryBuilder = $queryBuilder
                    ->join('s.participants','p','WITH','p.pseudo = :user')
                    //->join('s.participants','p')
                    //->orWhere('p.pseudo = :user')
                    ->setParameter('user',$user->getPseudo());
            }

            if (in_array('noninscrit',$filtres->typeSortie) && !in_array('inscrit',$filtres->typeSortie))
            {
                $expression = $this->getEntityManager()->getExpressionBuilder();

                $queryBuilder->andWhere($expression->notIn(
                    's.id',
                        // requete qui donne les sorties ou je suis inscrit
                        $this->getEntityManager()->createQueryBuilder()
                            ->select('sortie2.id')
                            ->from(Sortie::class, 'sortie2')
                            ->join('sortie2.participants', 'p2')
                            ->andWhere('p2 = :id')
                            ->getDQL()
                ))
                    ->setParameter(':id', $user->getId());

                /*$queryBuilder = $queryBuilder
                    ->leftJoin('s.participants','pa','WITH','pa.pseudo = :user')
                    //->join('s.participants','pa')
                    //->andWhere('pa.pseudo != :user')
                    ->setParameter('user',$user->getPseudo());*/
            }

            if (in_array('orga',$filtres->typeSortie))
            {

                $queryBuilder = $queryBuilder
                    ->join('s.organisateur', 'o','WITH', 'o.pseudo = :user')
                    //->join('s.organisateur','o')
                    //->orWhere('o.pseudo = :user')
                    ->setParameter('user',$user->getPseudo());
            }

            //$query = $queryBuilder->getQuery();
            //$query->getResult();

            if (!in_array('finies', $filtres->typeSortie))
            {
                $dateJour = new DateTime();
                $queryBuilder = $queryBuilder
                    ->andWhere('s.dateHeureDebut >= :dateJour')
                    ->setParameter('dateJour',$dateJour);
            }
            else
            {
                $dateJour = new DateTime();
                $queryBuilder = $queryBuilder
                    ->orWhere('s.dateHeureDebut < :dateJour')
                    ->setParameter('dateJour',$dateJour);
            }



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

            $queryBuilder->andWhere('s.dateHeureDebut >= :val')
                ->setParameter('val', $date);
            $queryBuilder->orderBy('s.dateHeureDebut', 'ASC');

        }

        else
        {
            $queryBuilder->andWhere('s.organisateur = :rien')
                ->setParameter('rien','riendutout');
        }

        $query = $queryBuilder->getQuery();
        $query->getResult();

        $paginator = new Paginator($query);

        return $paginator;
    }

    public function filtreListeSorties($user, $filtres, $date): Paginator
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->join('s.organisateur','o');

        if (in_array('inscrit',$filtres))
        {
            $queryBuilder = $queryBuilder
                ->join('s.participants','p')
                ->andWhere('p.id = :user')
                ->setParameter('user',$user->getId());
        }

        if (in_array('noninscrit',$filtres))
        {
            $expression = $this->getEntityManager()->getExpressionBuilder();

            $queryBuilder->andWhere($expression->notIn(
                's.id',
                // requete qui donne les sorties ou je suis inscrit
                $this->getEntityManager()->createQueryBuilder()
                    ->select('sortie2.id')
                    ->from(Sortie::class, 'sortie2')
                    ->join('sortie2.participants', 'p2')
                    ->andWhere('p2 = :id')
                    ->getDQL()
            ))
                ->andWhere('o.id != :id')
                ->setParameter(':id', $user->getId());

            /*$queryBuilder = $queryBuilder
                //->leftJoin('s.participants','pa')
                ->leftJoin('s.participants','pa','WITH','pa.id = :user')
                ->andWhere('o.id != :user')
                //->andWhere('pa.id <> :user')
                ->setParameter('user',$user->getId());*/
        }

        if (in_array('orga',$filtres))
        {
            $queryBuilder = $queryBuilder
                ->andWhere('o.id = :user')
                ->setParameter('user',$user->getId());
        }

        if (!in_array('finies', $filtres))
        {
            $dateJour = new DateTime();
            $queryBuilder = $queryBuilder
                ->andWhere('s.dateHeureDebut >= :dateJour')
                ->setParameter('dateJour',$dateJour);
        }
        else
        {
            $dateJour = new DateTime();
            $queryBuilder = $queryBuilder
                ->andWhere('s.dateHeureDebut < :dateJour')
                ->setParameter('dateJour',$dateJour);
        }

        if (array_key_exists('campus', $filtres) && $filtres['campus'] != "")
        {
            $queryBuilder = $queryBuilder
                ->andWhere('s.campus = :c')
                ->setParameter('c', $filtres['campus']);
        }


        if (array_key_exists('nom', $filtres) && $filtres['nom'] != "")
        {
            $queryBuilder = $queryBuilder
                ->andWhere('s.nom LIKE :q')
                ->setParameter('q', "%{$filtres['nom']}%");
        }

        if (array_key_exists('dateMin', $filtres) && $filtres['dateMin'] != "")
        {
            $queryBuilder = $queryBuilder
                ->andWhere('s.dateHeureDebut >= :min')
                ->setParameter('min', $filtres['dateMin']);
        }

        if (array_key_exists('dateMax', $filtres) && $filtres['dateMax'] != "")
        {
            $queryBuilder  = $queryBuilder
                ->andWhere('s.dateHeureDebut <= :max')
                ->setParameter('max', $filtres['dateMax']);
        }


        $queryBuilder = $queryBuilder
            ->andWhere('s.dateHeureDebut >= :val')
            ->setParameter('val', $date);
        $queryBuilder->orderBy('s.dateHeureDebut', 'ASC');

        $query = $queryBuilder->getQuery();
        $query->getResult();

        $paginator = new Paginator($query);

        return $paginator;
    }
}
