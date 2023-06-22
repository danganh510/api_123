<?php
namespace Score\Repositories;

use Phalcon\Mvc\User\Component;
use Score\Models\ScConfigMatch;
use Score\Repositories\CacheRepoNew;

class MatchDetailLocale extends Component
{

    public static function changeKeyToContentStart($arrStart, $language) {
        if (empty($arrStart)) {
            return [];
        }
        $ar_config = self::getAllConfig();
        if ($language == "en") {
            $ar_config = array_column($ar_config,"config_content_en","config_key");
        } else {
            $ar_config = array_column($ar_config,"config_content_vi","config_key");
        }
        $arrStartNew = [];        
        foreach ($arrStart as $start) {

            $dataTemp = [
                'category' => isset($ar_config[trim($start['category'])]) ? $ar_config[trim($start['category'])] : trim($start['category']),
                'homeValue' => $start['homeValue'],
                'awayValue' => $start['awayValue'],
            ];
            $arrStartNew[] = $dataTemp;
        }
        return $arrStartNew;
    }
    public static function changeContentToKeyStart($arrStart, $language) {
        if (empty($arrStart)) {
            return [];
        }
        $ar_config = self::getAllConfig();
        if ($language == "en") {
            $ar_config = array_column($ar_config,"config_key","config_crawl_en");
        } else {
            $ar_config = array_column($ar_config,"config_key","config_crawl_vi");
        }
        $arrStartNew = [];        
        foreach ($arrStart as $start) {

            $dataTemp = [
                'category' => isset($ar_config[trim($start['category'])]) ? $ar_config[trim($start['category'])] : trim($start['category']),
                'homeValue' => $start['homeValue'],
                'awayValue' => $start['awayValue'],
            ];
            $arrStartNew[] = $dataTemp;
        }
        return $arrStartNew;
    }
    public static function getAllConfig()
    {
        $cache = new CacheRepoNew("all_confic_match");
        $all_config = $cache->getCache();
        if (!$cache->getCache()) {
            $data = ScConfigMatch::find()->toArray();
            $all_config = $cache->setCache($data);
        }
        return $all_config;
    }
}
