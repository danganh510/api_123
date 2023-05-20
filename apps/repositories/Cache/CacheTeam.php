<?php

namespace Score\Repositories;

use ConstEnv;
use Phalcon\Mvc\User\Component;
use Phalcon\Cache\Backend\File as BackFile;
use Phalcon\Cache\Frontend\Data as FrontData;
use Score\Models\ScTeam;

class CacheTeam extends Component
{
    const PRE_SESSION_CACHE = "";
    const filePath = __DIR__ . "/../../Cache/Team/";
    static $frontCache = null;
    static $backCache = null;
    public function __construct()
    {
        if (!is_dir(__DIR__ . "/../../Cache")) {
            mkdir(__DIR__ . "/../../Cache");
        }
        if (!is_dir(self::filePath)) {
            mkdir(self::filePath);
        }
    }
    public function get($type)
    {
        $arrTeam = $this->getCache($type);
        if (empty($arrTeam)) {
            $arrTeam = ScTeam::find("team_active = 'Y'");
            $arrTeam = $arrTeam->toArray();
            $arrTeamCache = [];
            switch ($type) {
                case ConstEnv::CACHE_TYPE_ID:
                    foreach ($arrTeam as $team) {
                        $arrTeamCache[$team['team_id']] = $team;
                    }
                    break;
                case ConstEnv::CACHE_TYPE_NAME:
                    foreach ($arrTeam as $team) {
                        $arrTeamCache[$team['team_name']] = $team;
                    }
                    break;
                case ConstEnv::CACHE_TYPE_NAME_FLASH:
                    foreach ($arrTeam as $team) {
                        $arrTeamCache[$team['team_name_flashscore']] = $team;
                    }
                    break;
            }
            $this->setCache($arrTeamCache, $type);
            $arrTeam = $this->getCache($type);
        }
        return $arrTeam;
    }
    public function set($type)
    {
        $arrTeam = ScTeam::find("team_active = 'Y'");
        $arrTeam = $arrTeam->toArray();
        switch ($type) {
            case ConstEnv::CACHE_TYPE_ID:
                foreach ($arrTeam as $team) {
                    $arrTeamCache[$team['team_id']] = $team;
                }
                $teamCache = new CacheTeam();
                $teamCache->setCache($arrTeamCache, $type);
                break;
            case ConstEnv::CACHE_TYPE_NAME:
                foreach ($arrTeam as $team) {
                    $arrTeamCache[$team['team_name']] = $team;
                }
                $teamCache = new CacheTeam();
                $teamCache->setCache($arrTeamCache, $type);
                break;
            case ConstEnv::CACHE_TYPE_NAME_FLASH:
                foreach ($arrTeam as $team) {
                    $arrTeamCache[$team['team_name_flashscore']] = $team;
                }
                $teamCache = new CacheTeam();
                $teamCache->setCache($arrTeamCache, $type);
                break;
            default:
                foreach ($arrTeam as $team) {
                    $arrTeamCacheID[$team['team_id']] = $team;
                    $arrTeamCacheName[$team['team_name']] = $team;
                    $arrTeamCacheFLName[$team['team_name_flashscore']] = $team;
                }
                $teamCache = new CacheTeam();
                $teamCache->setCache($arrTeamCacheID, ConstEnv::CACHE_TYPE_ID);
                $teamCache->setCache($arrTeamCacheName, ConstEnv::CACHE_TYPE_NAME);
                $teamCache->setCache($arrTeamCacheFLName, ConstEnv::CACHE_TYPE_NAME_FLASH);
                break;
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
            self::$frontCache = new FrontData(['lifetime' => 24 * 60 * 60]);
        }

        return self::$frontCache;
    }

    //Load cache keys
    public function cacheKeyClients($sessionId)
    {
        return $sessionId . 'cls.data';
    }
}
