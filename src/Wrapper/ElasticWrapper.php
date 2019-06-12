<?php

namespace App\Wrapper;

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
     * @var $index
     */
    private $index;

    /**
     * @var $type
     */
    private $type;

    /**
     * @param $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $type
     */
    public function setIndex($index)
    {
        $this->index = $index;
    }

    /**
     * @return mixed
     * @author Mehran
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * This method has been change to private for no one can not create instance of class with new Keyword
     *
     * @return null
     * @author Mehran
     */
    private function __construct()
    {
        $this->elastic_client = ClientBuilder::create()->build();
        $this->setIndex('digi_project');
        $this->setType('product');

        $this->CheckingIndex();
        return null;
    }

    /**
     * This method return null object for no one can not create instance of class with new Keyword
     *
     * @return null
     * @author Mehran
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
     * @param array $data
     * @return array
     * @author Mehran
     */
    public function searchIndex(array $data): array
    {
        return $this->searchRequest($data);
    }

    /**
     * This method has been used for create Elastic query according to input data
     *
     * @param array $data
     * @return array
     * @author Mehran
     */
    protected function createElasticQuery(array $data): array
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
                'variants.price' => $data['price']
            ];
        }
        if (!is_null($data['color'])) {
            $match_data[]['match'] = [
                'variants.color' => $data['color']
            ];
        }

        return $match_data;
    }

    /**
     * This method has been used for search in Elastic db according to input data
     *
     * @param array $data
     * @return array
     * @author Mehran
     */
    protected function searchRequest(array $data): array
    {
        $query = $this->createElasticQuery($data);
        $params = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
            'body' => $query//gray
        ];
        $response = $this->elastic_client->search($params);
        return $response['hits']['hits'];
    }

    /**
     * This Method has been used for delete special document from document_id
     *
     * @param int $document_id
     * @return bool
     * @author Mehran
     */
    public function deleteDocument(int $document_id)
    {
        $params = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
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
     * @author Mehran
     */
    public function updateDocument(int $document_id, array $data)
    {
        $params = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
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
     * @author Mehran
     */
    public function indexDocument(int $document_id, array $data)
    {
        $params = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
            'id' => $document_id,
            'body' => $data

        ];
        $this->elastic_client->index($params);

        return true;
    }

    /**
     * This Methd has been used for check current index exist or not
     * If current index is not exist make it
     *
     * @return bool
     * @author Mehran
     */
    public function CheckingIndex()
    {
        $params = [
            'index' => $this->getIndex(),
        ];
        $response = $this->elastic_client->indices()->exists($params);
        if (!$response) {
            $this->initializeIndex();
        }

        return true;
    }

    /**
     * This method has been used for initialize new index
     *
     * @return bool
     * @author Mehran
     */
    public function initializeIndex()
    {
        $params = [
            'index' => $this->getIndex(),
        ];
        $this->elastic_client->indices()->create($params);

        return true;
    }
}
