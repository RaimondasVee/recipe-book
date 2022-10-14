<?php

namespace App\Repository;

use App\Entity\Recipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recipe>
 *
 * @method Recipe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Recipe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Recipe[]    findAll()
 * @method Recipe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recipe::class);
    }

    public function save(Recipe $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Recipe $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAccessibleRecipes(): array
    {
        $value = 'public';

        return $this->createQueryBuilder('r')
            ->andWhere('r.visibility = :val')
            ->setParameter('val', $value)
            ->orderBy('r.name', 'ASC')
            // ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findPersonalRecipes($value): array
    {

        return $this->createQueryBuilder('r')
            ->andWhere('r.author = :val')
            ->setParameter('val', $value)
            ->orderBy('r.name', 'ASC')
            // ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    public function MySqlFindVisibleAndExcludingIDs(int $author, array $notIdsArr): mixed
    {
        // Prepare Nots
        if (count($notIdsArr) === 0) {
            $notIdsArr = 0;
        } else {
            $notIdsArr = implode(', ', $notIdsArr);
        }

        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT * FROM recipe.recipe
            WHERE author = :author
            AND NOT id in (' . $notIdsArr . ')
            UNION
            SELECT * FROM recipe.recipe
            WHERE visibility = \'public\'
            AND NOT id in (' . $notIdsArr . ')
            ORDER BY name ASC
            ';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['author' => $author]);

        // returns an array of arrays (i.e. a raw data set)
        return $resultSet->fetchAllAssociative();
        











        // $entityManager = $this->getEntityManager();

        // // Prepare Nots
        // if (count($notIdsArr) === 0) {
        //     $notIdsArr = 0;
        // } else {
        //     $notIdsArr = implode(', ', $notIdsArr);
        // }

        // var_dump($notIdsArr);
        

        // $query = $entityManager->createQuery(
        //     'SELECT r
        //     FROM App\Entity\Recipe r
        //     WHERE r.author = :author AND NOT r.id IN (:notIdsArr)
        //     UNION
        //     SELECT r
        //     FROM App\Entity\Recipe r
        //     WHERE r.visibility = \'public\' AND NOT r.id IN (:notIdsArr)
        //     ORDER BY r.name ASC'
        // )->setParameter('author', $author)
        //  ->setParameter('notIdsArr', $notIdsArr);

        //  var_dump($query->getParameters());

        // return $query->getResult();
    }

//    /**
//     * @return Recipe[] Returns an array of Recipe objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Recipe
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
