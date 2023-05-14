<?php

namespace Score\Repositories;

use ConstEnv;
use Phalcon\Mvc\User\Component;
use Phalcon\Cache\Backend\File as BackFile;
use Phalcon\Cache\Frontend\Data as FrontData;
use Score\Models\ScTournament;

class CacheGetData extends Component
{
    const PRE_SESSION_CACHE = "";
    const filePath = __DIR__ . "/../../Cache/ApiData/";
    static $frontCache = null;
    static $backCache = null;
    public $key;
    public function __construct($param = [])
    {
        if (!is_dir(__DIR__ . "/../Cache")) {
            mkdir(__DIR__ . "/../Cache");
        }
        if (!is_dir(self::filePath)) {
            mkdir(self::filePath);
        }
        $this->key = "";
        if (!empty($param)) {
            foreach ($param as $key => $value) {
                $value = trim(json_encode($value));
                $value = preg_replace('/[^a-zA-Z0-9]/s', '', $value);
                $this->key .= "_$value";
            }
        }
    }
    public function deleteFolder()
    {
        $dirname = self::filePath;
        if (is_dir($dirname))
            $dir_handle = opendir($dirname);
        if (!$dir_handle)
            return false;

        while ($file = readdir($dir_handle)) {
            if ($file != "." && $file != "..") {
                if (!is_dir($dirname . "/" . $file))
                    unlink($dirname . "/" . $file);
            }
        }

        closedir($dir_handle);
        rmdir($dirname);
        return true;
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
    public  function getCache()
    {
        $sessionId = self::PRE_SESSION_CACHE . $this->key;

        $cache = self::getBackCache();
        $cacheKey = self::cacheKeyClients($sessionId);
        $clients = $cache->get($cacheKey);

        return json_decode($clients, true);
    }
    public  function deleteCache()
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
            $result =  $cache->save($cacheKey, json_encode($data));
        } catch (\Exception $e) {
            return false;
        }

        // $result =  $cache->set($cacheKey);
        return $result;
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
            self::$frontCache = new FrontData(['lifetime' => 48 * 60 * 60]);
        }

        return self::$frontCache;
    }

    //Load cache keys
    public function cacheKeyClients($sessionId)
    {
        return $sessionId . 'cls.data';
    }
}
