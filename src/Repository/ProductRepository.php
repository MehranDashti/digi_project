<?php

namespace App\Repository;

use App\Entity\Product;
use App\Wrapper\ElasticWrapper;
use App\Wrapper\RedisWrapper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;

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
     * @param Product $data
     * @return array
     * @author Mehran
     */
    public function fetchProduct(Product $data = null): array
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

        return $this->searchElasticIndex($data);
    }

    /**
     * @param Product $data
     * @return array
     * @author Mehran
     */
    public function searchElasticIndex(Product $data):array
    {
        $response = ElasticWrapper::search()->searchIndex($data);
        if (!empty($response)) {
            foreach ($response as $key => $hit) {
                $cacheProduct = RedisWrapper::action()->fetchCacheData('product_' . $hit['_id']);
                if (empty($cacheProduct)) {
                    $cacheProduct = $this->setProductCache($hit['__id']);
                } else {
                    $cacheProduct = json_decode($cacheProduct, true);
                }
                $result[$key] = array_merge($cacheProduct, ['id' => $hit['_id']]);
            }
            return $result;
        }
        return [];
    }


    /**
     * @param integer $product_id
     * @return array
     * @author Mehran
     */
    protected function setProductCache(Integer $product_id): array
    {
        $product = $this->findOneBy(['id' => $product_id]);
        $productData = [
            'title' => $product->getTitle(),
            'description' => $product->getDescription()
        ];
        RedisWrapper::action()->initializeCache('product_' . $product->getId(), $productData);

        return $productData;
    }
}
