<?php

namespace Score\Models;

use Phalcon\Events\Manager as EventsManager;
use Phalcon\Events\Event;

class ScConfig extends \Phalcon\Mvc\Model
{
    // public function initialize()
    // {
    //     $eventsManager = new EventsManager();
    //     $eventsManager->attach('model', function ($event, $model) {
    //         /**
    //          * @var $model Model
    //          * @var $event Event
    //          */
        
    //         if ($event->getType() == "afterSave" || $event->getType() == "afterDelete") {
    //             $urlDelete =  defined('TEST_MODE') && TEST_MODE ? "https://sandbox.travelner.com" : "https://www.travelner.com";
    //             $URL_DELETE_CACHE_TOOL = $urlDelete . '/delete-cache';
    //             $URL_DELETE_CACHE_TOOL .= '?type=configCache';
    //             $ch = curl_init();
    //             curl_setopt($ch, CURLOPT_HEADER, 0);
    //             curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //             curl_setopt($ch, CURLOPT_URL, $URL_DELETE_CACHE_TOOL);
    //             curl_setopt($ch, CURLOPT_POST, 1);
    //             curl_setopt(
    //                 $ch,
    //                 CURLOPT_POSTFIELDS,
    //                 "ctoken=k3FRQ1U0bYHUVSu6"
    //             );
    //             $data = curl_exec($ch);
    //             curl_close($ch);
    //         }
    //     });
    //     $this->setEventsManager($eventsManager);
    // }



    /**
     *
     * @var string
     * @Primary
     * @Column(type="string", length=255, nullable=false)
     */
    protected $config_key;

    /**
     *
     * @var string
     * @Primary
     * @Column(type="string", length=5, nullable=false)
     */
    protected $config_language;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $config_content;

    /**
     * Method to set the value of field config_key
     *
     * @param string $config_key
     * @return $this
     */
    public function setConfigKey($config_key)
    {
        $this->config_key = $config_key;

        return $this;
    }

    /**
     * Method to set the value of field config_language
     *
     * @param string $config_language
     * @return $this
     */
    public function setConfigLanguage($config_language)
    {
        $this->config_language = $config_language;

        return $this;
    }

    /**
     * Method to set the value of field config_content
     *
     * @param string $config_content
     * @return $this
     */
    public function setConfigContent($config_content)
    {
        $this->config_content = $config_content;

        return $this;
    }

    /**
     * Returns the value of field config_key
     *
     * @return string
     */
    public function getConfigKey()
    {
        return $this->config_key;
    }

    /**
     * Returns the value of field config_language
     *
     * @return string
     */
    public function getConfigLanguage()
    {
        return $this->config_language;
    }

    /**
     * Returns the value of field config_content
     *
     * @return string
     */
    public function getConfigContent()
    {
        return $this->config_content;
    }

    /**
     * Initialize method for model.
     */
    //    public function initialize()
    //    {
    //        $this->setSchema("adminkhaihungvn");
    //    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'sc_config';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ScConfig[]|ScConfig
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ScConfig
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }
    public static function getConfig($key, $lang = 'en')
    {
        $configs = ScConfig::findFirst(array(
            "config_language = '$lang' 
            AND config_key = '$key'
            ",
        ));
        if ($configs && sizeof($configs) > 0) return $configs->getConfigContent();
        return '';
    }
}
