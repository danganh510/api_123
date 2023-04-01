<?php

namespace Score\Repositories;

use ConstEnv;
use Phalcon\Mvc\User\Component;
use Phalcon\Cache\Backend\File as BackFile;
use Phalcon\Cache\Frontend\Data as FrontData;
use Score\Models\ScTournament;

class CacheTour extends Component
{
    const PRE_SESSION_CACHE = "";
    const filePath = __DIR__ . "/../Cache/Tour/";
    static $frontCache = null;
    static $backCache = null;
    public function __construct()
    {
        if (!is_dir(__DIR__ . "/../Cache")) {
            mkdir(__DIR__ . "/../Cache");
        }
        if (!is_dir(self::filePath)) {
            mkdir(self::filePath);
        }
    }
    public function get($type)
    {
        $arrTour = $this->getCache($type);
        if (empty($arrTeam)) {
            $arrTour = ScTournament::find("tournament_active = 'Y'");
            $arrTour = $arrTour->toArray();
            $arrTourCache = [];

            switch ($type) {
                case ConstEnv::CACHE_TYPE_ID:
                    foreach ($arrTour as $tour) {
                        $arrTourCache[$tour['tournament_id']] = $tour;
                    }
                    break;
                case ConstEnv::CACHE_TYPE_NAME:
                    foreach ($arrTour as $tour) {
                        $arrTourCache[$tour['tournament_name'] . "_" . $tour['tournament_country_code']] = $tour;
                    }
                    break;
                case ConstEnv::CACHE_TYPE_NAME_FLASH:
                    foreach ($arrTour as $tour) {
                        $arrTourCache[$tour['tournament_name_flash_score'] . "_" . $tour['tournament_country_code']] = $tour;
                    }
                    break;
            }
            $tourCache = new CacheTour();
            $result = $tourCache->setCache($arrTourCache, $type);
            $arrTour = $tourCache->getCache($type);
        }
        return $arrTour;
    }
    public function set($type)
    {
        $arrTour = $this->getCache($type);
        if (empty($arrTeam)) {
            $arrTour = ScTournament::find("tournament_active = 'Y'");
            $arrTour = $arrTour->toArray();
            $arrTourCache = [];

            switch ($type) {
                case ConstEnv::CACHE_TYPE_ID:
                    foreach ($arrTour as $tour) {
                        $arrTourCache[$tour['tournament_id']] = $tour;
                    }
                    $tourCache = new CacheTour();
                    $tourCache->setCache($arrTourCache, ConstEnv::CACHE_TYPE_ID);
                    break;
                case ConstEnv::CACHE_TYPE_NAME:
                    foreach ($arrTour as $tour) {
                        $arrTourCache[$tour['tournament_name'] . "_" . $tour['tournament_country_code']] = $tour;
                    }
                    $tourCache = new CacheTour();
                    $tourCache->setCache($arrTourCache, ConstEnv::CACHE_TYPE_ID);
                    break;
                case ConstEnv::CACHE_TYPE_NAME_FLASH:
                    foreach ($arrTour as $tour) {
                        $arrTourCache[$tour['tournament_name_flash_score'] . "_" . $tour['tournament_country_code']] = $tour;
                    }
                    $tourCache = new CacheTour();
                    $tourCache->setCache($arrTourCache, ConstEnv::CACHE_TYPE_ID);
                    break;
                default:
                    foreach ($arrTour as $tour) {
                        $arrTourCache[$tour['tournament_id']] = $tour;
                        $arrTourCacheName[$tour['tournament_name'] . "_" . $tour['tournament_country_code']] = $tour;
                        $arrTourCacheFlashName[$tour['tournament_name_flash_score'] . "_" . $tour['tournament_country_code']] = $tour;
                    }
                    $tourCache = new CacheTour();
                    $tourCache->setCache($arrTourCache, ConstEnv::CACHE_TYPE_ID);
                    $tourCache = new CacheTour();
                    $tourCache->setCache($arrTourCacheName, ConstEnv::CACHE_TYPE_NAME);
                    $tourCache = new CacheTour();
                    $tourCache->setCache($arrTourCacheFlashName, ConstEnv::CACHE_TYPE_NAME_FLASH);
            }
        }
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
    public  function getCache($type)
    {
        $sessionId = self::PRE_SESSION_CACHE . $type;

        $cache = self::getBackCache();
        $cacheKey = self::cacheKeyClients($sessionId);
        $clients = $cache->get($cacheKey);

        return json_decode($clients, true);
    }
    public  function deleteCache($type)
    {
        $sessionId = self::PRE_SESSION_CACHE . $type;
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
    public function setCache($arrTeam, $type)
    {
        $sessionId = self::PRE_SESSION_CACHE . $type;
        $cache = self::getBackCache();
        $cacheKey = $this->cacheKeyClients($sessionId);
        try {
            if (!is_dir(self::filePath)) {
                mkdir(self::filePath);
            }
            $result =  $cache->save($cacheKey, json_encode($arrTeam));
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
