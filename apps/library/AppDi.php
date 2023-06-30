<?php

namespace Score\Library;

use App\Services\AchievementService;
use Library\AppCacheManager;
use Library\Auth\Auth;
use Phalcon\Di;
use Phalcon\Mvc\Model\Manager;
use Phalcon\Mvc\Url\UrlInterface;
use Phalcon\Session\Manager as SessionRedis;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Logger\Logger;
use Aws\S3\S3Client;
use Library\Plugin\TranslatorPlugin;

class AppDI 
{
    public static function get()
    {
        return Di::getDefault();
    }

    public static function getService($service_name, $is_shared = false)
    {
        $di = self::get();

        if ($is_shared)
            return $di->getShared($service_name);
        return $di->get($service_name);
    }

    public static function getCache(): AppCacheManager
    {
        return self::getService(DI_SERVICE_CACHE, true);
    }

    public static function getRedisCache()
    {
        try {
            return self::getCache()->getAdapter()->getAdapter();
        } catch (\Phalcon\Storage\Exception $e) {
            throw $e;
        }
    }

    public static function getSessionCache(): AppCacheManager
    {
        return self::getService(DI_SERVICE_SESSION_CACHE);
    }

    public static function getMasterCache(): AppCacheManager
    {
        return self::getService(DI_SERVICE_MASTER_CACHE);
    }

    public static function getUrl(): UrlInterface
    {
        return self::getService(DI_SERVICE_URL, true);
    }

    public static function getModelManager(): Manager
    {
        return AppDI::getService(DI_SERVICE_MODELSMANAGER);
    }

    public static function getLogger(): Logger
    {
        return AppDI::getService(DI_SERVICE_LOGGER, true);
    }

    public static function getSession(): SessionRedis
    {
        return AppDI::getService(DI_SERVICE_SESSION);
    }

    public static function getAuth(): Auth
    {
        return AppDI::getService(DI_SERVICE_AUTH, true);
    }

    public static function getDate(): AppDate
    {
        return AppDI::getService(DI_SERVICE_DATE, true);
    }

    public static function getDb($database_source_name): Mysql
    {
        $di_name = 'db' . ucfirst(strtolower($database_source_name));
        return AppDI::getService($di_name);
    }

    public static function getAchievement(): AchievementService
    {
        return AppDI::getService(DI_SERVICE_ACHIEVEMENT, true);
    }

    public static function getMissionCache(): AppCacheManager
    {
        return AppDI::getService(DI_SERVICE_MISSION_CACHE, true);
    }

    public static function getAwsClient(): S3Client
    {
        return AppDI::getService(DI_SERVICE_AWS, true);
    }

    public static function getTranslator(): TranslatorPlugin
    {
        return AppDI::getService(DI_SERVICE_LOCATE, true);
    }
}
