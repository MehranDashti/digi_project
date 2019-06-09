<?php

namespace App\Wrapper;

use Symfony\Component\Cache\Adapter\RedisAdapter;

/**
 * Class RedisWrapper
 * @package App\Wrapper
 * @author Mehran
 */
class RedisWrapper
{
    /**
     * @var $instance
     */
    private static $instance;

    /**
     * @var $redisClient
     */
    protected $redisClient;

    /**
     * This method has been change to private for no one can not create instance of class with new Keyword
     *
     * @return null
     */
    private function __construct()
    {
        $this->redisClient = RedisAdapter::createConnection(
            'redis://localhost'
        );
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
     * @return RedisWrapper
     * @author Mehran
     */
    public static function action()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * This method has been used for delete from cache according to particular key
     *
     * @param $key
     * @return bool
     * @author Mehran
     */
    public function deleteCache($key): bool
    {
        $cache_value = $this->redisClient->get($key);
        if (!empty($cache_value)) {
            $this->redisClient->del($key);
        }

        return true;
    }

    /**
     * This method has been used for initial new cache or update cache with particular key
     *
     * @param $key
     * @param $data
     * @param bool $update_flag
     * @return bool
     * @author Mehran
     */
    public function initializeCache($key , $data, $update_flag = false): bool
    {
        if ($update_flag) {
            $this->deleteCache($key);
        }
        if (is_array($data)) {
            $data = json_encode($data);
        }
        $this->redisClient->set($key, $data);
        return true;
    }

    /**
     * This method has been used for fetch particular data from cache
     *
     * @param $key
     * @return bool|string|array|int
     * @author Mehran
     */
    public function fetchCacheData($key)
    {
        return $this->redisClient->get($key);
    }
}
