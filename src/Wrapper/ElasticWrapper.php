<?php

namespace App\Wrapper;

use Elasticsearch\ClientBuilder;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class ElasticWrapper
{
    /**
     * @var $instance
     */
    private static $instance;

    /**
     * @var $redis_client
     */
    protected $redis_client;
    /**
     * @var $entityManager
     */
    protected $entityManager;

    /**
     * This method has been change to private for no one can not create instance of class with new Keyword
     *
     * @return null
     */
    private function __construct()
    {
        $this->redis_client = RedisAdapter::createConnection(
            'redis://localhost'
        );
        $this->entityManager = null;
        return null;
    }

    /**
     * This method return null object for no one can not create instance of class with new Keyword
     *
     * @return null
     */
    public function __clone()
    {
        return null;
    }

    /**
     * This method has been used for return object of current class
     *
     * @return ElasticWrapper
     * @author Mehran
     */
    public static function search()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * This method has been used for search in Elastic db according to :
     * Product Title
     * Product Description
     * Variant Color
     * Variant Price
     *
     * @param $data
     * @return array
     * @author Mehran
     */
    public function searchIndex($data): array
    {
        $data = $this->initializeDataSearch($data);
        $query = $this->createElasticQuery($data);

        return $this->searchRequest($query);
    }

    /**
     * This Method has been used for prepare search data and fetch it from entity
     *
     * @param $data
     * @return array
     * @author Mehran
     */
    protected function initializeDataSearch($data): array
    {
        return [
            'title' => $data->getTitle(),
            'description' => $data->getDescription(),
            'color' => $data->getVariants()->toArray()['__name__']->getColor(),
            'price' => $data->getVariants()->toArray()['__name__']->getPrice(),
        ];
    }

    /**
     * This method has been used for create Elastic query according to input data
     *
     * @param $data
     * @return array
     * @author Mehran
     */
    protected function createElasticQuery($data): array
    {
        $query = [
            'query' => [
                'bool' => [
                    'should' => $this->matchData($data)
                ]
            ]
        ];

        return $query;
    }

    /**
     * This method has been used for create match data and apply conditions to data
     *
     * @param $data
     * @return array
     * @author Mehran
     */
    protected function matchData($data): array
    {
        $match_data = [];
        if (!is_null($data['title'])) {
            $match_data[]['match'] = [
                'title' => $data['title']
            ];
        }
        if (!is_null($data['description'])) {
            $match_data[]['match'] = [
                'description' => $data['description']
            ];
        }
        if (!is_null($data['price'])) {
            $match_data[]['match'] = [
                'variant.price' => $data['price']
            ];
        }
        if (!is_null($data['color'])) {
            $match_data[]['match'] = [
                'variant.color' => $data['color']
            ];
        }

        return $match_data;
    }

    /**
     * This method has been used for search in Elastic db according to input data
     *
     * @param $query
     * @return array
     * @author Mehran
     */
    protected function searchRequest($query): array
    {
        $result = [];
        echo "<pre>";
        $params = [
            'index' => 'digi_project',
            'type' => 'product',
            'body' => $query
        ];
        $client = $client = ClientBuilder::create()->build();
        $response = $client->search($params);

        if (!empty($response['hits']['hits'])) {
            foreach ($response['hits']['hits'] as $key => $hit) {
                $cacheProduct = $this->redis_client->get('product_' . $hit['_id']);
                if (empty($cacheProduct)) {
                    $cacheProduct = $this->setProductCache($hit['__id']);
                } else {
                    $cacheProduct = json_decode($cacheProduct, true);
                }
                $result[$key] = array_merge($cacheProduct, ['id' => $hit['_id']]);
            }
        }
        return $result;
    }

    /**
     * @param $product_id
     * @return array
     * @author Mehran
     */
    protected function setProductCache($product_id): array
    {
        $product = null;
        $productData = [
            'title' => $product->getTitle(),
            'description' => $product->getDescription()
        ];
        $this->redis_client->set('product_' . $product->getId(), json_encode($productData));

        return $productData;
    }
}