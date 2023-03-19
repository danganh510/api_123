<?php

namespace Score\Repositories;

use Phalcon\Mvc\User\Component;
use Score\Models\ScConfig;
use Score\Models\ScLanguage;

class ConfigContent extends Component
{
    const FOLDER ="/../messages/";
    const FILE_CACHED_CONFIG = "cached_config.txt";
    public static function findByID($key){
        return ScConfig::findFirst(array(
                "config_key =:key:",
                'bind' => array('key' => $key))
        );
    }
    public static function findByLanguage($key,$language){
        return ScConfig::findFirst(array("config_key =:key:  AND config_language=:language:",
            'bind' => array('key' => $key,'language'=>$language)));
    }

    /**
     * @param $key
     * @return ScConfig|ScConfig[]
     */
    public static function getByID($key){
        return ScConfig::find(array(
                "config_key =:key:",
                'bind' => array('key' => $key))
        );
    }
    public static function deletedByKey($key){
        $list_config = self::getByID($key);
        foreach ($list_config as $config){
            $config->delete();
        }
        return true;
    }
    public static function deleteCache(){
        $list_lang = ScLanguage::find();
        foreach ($list_lang as $lang){
            self::deleteCacheLanguage($lang->getLanguageCode());
        }
    }
    public static function deleteCacheLanguage($language){
        $type_language =$language."/";
        $cachedConfigFileName = __DIR__.self::FOLDER.$type_language.self::FILE_CACHED_CONFIG;
        if (file_exists($cachedConfigFileName)) {
            unlink($cachedConfigFileName);
        }
    }
    public static function curl_get_contents($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            "ctoken=ThanhLongBinCorp0292");
        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }
    public static function getCache($language, $define = true, $lang_url = "") {
        $type_language =$language."/";
        $folder =__DIR__.self::FOLDER.$type_language;
        $cachedConfigFileName = $folder.self::FILE_CACHED_CONFIG;
        if (file_exists($cachedConfigFileName)) {
            /*$messageArray = unserialize(file_get_contents($cachedConfigFileName), ["allowed_classes" => true]);*/
            $messageArray = unserialize(file_get_contents($cachedConfigFileName));
        } else {
            if (!is_dir($folder)) {
                /* mkdir($folder, 0777, TRUE,NULL); */
                mkdir($folder, 0777,TRUE);
            }
            $messages = self::getAllByLanguage($language);
            $messageArray = [];
            foreach($messages as $message) {
                $messageArray[$message->getConfigKey()] = $message->getConfigContent();
            }
            file_put_contents($cachedConfigFileName, serialize($messageArray));
        }
        if ($define == false) {
            return $messageArray;
        }
        else {
            foreach($messageArray as $key => $value) {
                if (!defined($key)) {
                    define($key, str_replace(array('|||LANG|||','|||SCRIPTBEFORE|||', '|||SCRIPTAFTER|||', '|||NOSCRIPTBEFORE|||', '|||NOSCRIPTAFTER|||'),array($lang_url,'<script>', '</script>', '<noscript>', '</noscript>'),$value));
                }
            }
        }
    }
    public static function getAllByLanguage($language){
        return ScConfig::find(array("config_language = :language:",
                'bind' => array('language'=> $language))
        );
    }

    /**
     * @param string $key
     * @return string
     */
    public static function getContent($key, $default = '', $lang   = 'en'){
        $scconfig  = self::findByLanguage($key,$lang);
        return !$scconfig ? $default : $scconfig->getConfigContent();
    }
}




