<?php

namespace App\Repository;

use App\Entity\Product;
use App\Wrapper\ElasticWrapper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function fetchProduct($data = null)
    {
        $result = [];
        if (is_null($data)) {
            $products = $this->findAll();
            foreach ($products as $key => $product) {
                $result[$key]['id'] = $product->getId();
                $result[$key]['title'] = $product->getTitle();
                $result[$key]['description'] = $product->getDescription();
            }

            return $result;
        }

        return ElasticWrapper::search()->searchIndex($data);
    }


}
