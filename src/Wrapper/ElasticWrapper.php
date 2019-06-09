<?php

namespace App\Wrapper;

use App\Entity\Product;
use Elasticsearch\ClientBuilder;

/**
 * Class ElasticWrapper
 * @package App\Wrapper
 * @author Mehran
 */
class ElasticWrapper
{
    /**
     * @var $instance
     */
    private static $instance;

    /**
     * @var $elastic_client
     */
    protected $elastic_client;

    /**
     * This method has been change to private for no one can not create instance of class with new Keyword
     *
     * @return null
     */
    private function __construct()
    {
        $this->elastic_client = ClientBuilder::create()->build();
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
     * @param Product $data
     * @return array
     * @author Mehran
     */
    public function searchIndex(Product $data): array
    {
        return $this->searchRequest($data);
    }

    /**
     * This Method has been used for prepare search data and fetch it from entity
     *
     * @param $data
     * @return array
     * @author Mehran
     */
    protected function initializeDataSearch(Product $data): array
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
     * @param Product $data
     * @return array
     * @author Mehran
     */
    protected function createElasticQuery(Product $data): array
    {
        $data = $this->initializeDataSearch($data);
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
     * @param array $data
     * @return array
     * @author Mehran
     */
    protected function matchData(array $data): array
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
     * @param Product $data
     * @return array
     * @author Mehran
     */
    protected function searchRequest(Product $data): array
    {
        $query = $this->createElasticQuery($data);
        $params = [
            'index' => 'digi_project',
            'type' => 'product',
            'body' => $query
        ];
        $response = $this->elastic_client->search($params);

        return $response['hits']['hits'];
    }

    /**
     * This Method has been used for delete special document from document_id
     *
     * @param int $document_id
     * @return bool
     */
    public function deleteDocument(int $document_id)
    {
        $params = [
            'index' => 'digi_project',
            'type' => 'product',
            'id' => $document_id,
        ];
        $this->elastic_client->delete($params);

        return true;
    }

    /**
     * This Method has been used for update special document from document_id
     *
     * @param int $document_id
     * @param array $data
     * @return bool
     */
    public function updateDocument(int $document_id, array $data)
    {
        $params = [
            'index' => 'digi_project',
            'type' => 'product',
            'id' => $document_id,
            'body' => [
                'doc' => $data
            ]
        ];
        $this->elastic_client->update($params);

        return true;
    }

    /**
     * This Method has been used for index new document
     *
     * @param int $document_id
     * @param array $data
     * @return bool
     */
    public function indexDocument(int $document_id, array $data)
    {
        $params = [
            'index' => 'digi_project',
            'type' => 'product',
            'id' => $document_id,
            'body' => $data

        ];
        $this->elastic_client->index($params);

        return true;
    }
}
