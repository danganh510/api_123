<?php

namespace Score\Repositories;

use Phalcon\Mvc\User\Component;
use Phalcon\Cache\Backend\File as BackFile;
use Phalcon\Cache\Frontend\Data as FrontData;

class CacheRepoNew extends Component
{
    const PRE_SESSION_CACHE = "";
    const filePath = __DIR__ . "/../Cache/";
    static $frontCache = null;
    static $backCache = null;
    public $key;
    public function __construct($key)
    {
        $this->key = $key;
    }
    public function clearCacheExpried()
    {
        $cacheKeys = self::$backCache->queryKeys();
        foreach ($cacheKeys as $key) {
            $content = self::$backCache->get($key);
            if ($content === null) {
                self::$backCache->delete($key);
            }
        }
    }
    public function getCache()
    {
        $sessionId = self::PRE_SESSION_CACHE . $this->key;

        $cache = self::getBackCache();
        $cacheKey = self::cacheKeyClients($sessionId);
        $clients = $cache->get($cacheKey);

        return json_decode($clients, true);
    }
    public function deleteCache()
    {
        $sessionId = self::PRE_SESSION_CACHE . $this->key;
        $cache = self::getBackCache();
        $cacheKey = $this->cacheKeyClients($sessionId);

        try {
            if (!is_dir(self::filePath)) {
                mkdir(self::filePath);
            }
            $result =  $cache->delete($cacheKey);
        } catch (\Exception $e) {
            return false;
        }

        // $result =  $cache->set($cacheKey);
        return $result;
    }
    public function setCache($data)
    {
        $sessionId = self::PRE_SESSION_CACHE . $this->key;
        $cache = self::getBackCache();
        $cacheKey = $this->cacheKeyClients($sessionId);
        try {
            if (!is_dir(self::filePath)) {
                mkdir(self::filePath);
            }
            $data_cache = json_encode($data, true);
            $result =  $cache->save($cacheKey, $data_cache);
        } catch (\Exception $e) {
            return false;
        }

        // $result =  $cache->set($cacheKey);
        return json_decode($data_cache, true);
    }
    public static function getBackCache()
    {
        $frontCache = self::getFrontCache();
        if (self::$backCache == null) {
            self::$backCache = new BackFile($frontCache, ['cacheDir' => self::filePath,]);
        }

        return self::$backCache;
    }

    public static function getFrontCache()
    {
        if (self::$frontCache == null) {
            self::$frontCache = new FrontData(['lifetime' => 30 * 24 * 60 * 60]);
        }

        return self::$frontCache;
    }

    //Load cache keys
    public function cacheKeyClients($sessionId)
    {
        return $sessionId . 'cls.data';
    }
}
