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

    /**
     * This method has been used for fetch product in two way:
     *  One : When user load first page and see all product
     *  Second : When user search particular product according to title, description, color and price
     *
     * @param null $data
     * @return array
     * @author Mehran
     */
    public function fetchProduct($data = null): array
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
