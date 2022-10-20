<?php

namespace App\Repository;

use App\Entity\Gandalf;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Gandalf>
 *
 * @method Gandalf|null find($id, $lockMode = null, $lockVersion = null)
 * @method Gandalf|null findOneBy(array $criteria, array $orderBy = null)
 * @method Gandalf[]    findAll()
 * @method Gandalf[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GandalfRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Gandalf::class);
    }

    public function save(Gandalf $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Gandalf $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllWithPagination($page, $limit) {
        $qb = $this->createQueryBuilder('g')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
        return $qb->getQuery()->getResult();
    }
}
