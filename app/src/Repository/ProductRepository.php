<?php

namespace App\Repository;

use App\DTO\ProductsIndexRequestDTO;
use App\Entity\Category;
use App\Entity\Product;
use App\Services\OffsetPaginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function save(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function searchWithPagination(ProductsIndexRequestDTO $DTO): array
    {
        $query = $this->createQueryBuilder('a');
        if ($DTO->getCategoryId()) {
            $query = $query->where('a.category = :category')
                ->setParameter('category', $DTO->getCategoryId());
        }

        if ($DTO->getTitle()) {
            $query = $query->{$DTO->getCategoryId() ? 'andWhere' : 'where'}('a.title LIKE :title')
                ->setParameter('title', '%' . $DTO->getTitle() . '%');
        }

        $query->orderBy('a.' . $DTO->getOrderBy(), $DTO->getOrderDirection());
//        dd($query->getQuery());

        $paginator = new OffsetPaginator($query->getQuery());

        return [
            'products' => $paginator->getPage($DTO->getPage())->getResult(),
            'all' => $paginator->getCount(),
            'pages' => $paginator->getCountPages(),
            'page' => $DTO->getPage()
        ];
    }

    public function createProductsWithCategoriesFromXml(array $data, int $chunkSize = 50)
    {
        $categoryRepository = $this->getEntityManager()->getRepository(Category::class);
        $idx = 0;
        foreach ($data as $item) {
            $product = $this->findOrCreateByTitle($item['name']);
            $product->setDescription($item['description']);
            $product->setWeightFromString($item['weight']);

            $category = $categoryRepository->findOrCreateByTitle($item['category']);
            $product->setCategory($category);

            $this->getEntityManager()->persist($product);

            if ($idx % $chunkSize === 0) {
                $this->getEntityManager()->flush();
            }
        }
    }

    public function findOrCreateByTitle(string $title): Product
    {
        if (($product = $this->findOneBy(['title' => $title])) === null) {
            $product = new Product();
            $product->setTitle($title);
        }

        return $product;
    }

//    /**
//     * @return Product[] Returns an array of Product objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Product
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
