<?php

namespace Score\Models;

class ScConfigMatch extends \Phalcon\Mvc\Model
{

    public $config_key;
    public $config_content_en;
    public $config_content_vi;
    public $config_crawl_en;
    public $config_crawl_vi;

    public function getSource()
    {
        return 'sc_config_match';
    }

    // Hàm set cho config_key
    public function setConfigKey($config_key)
    {
        $this->config_key = $config_key;
    }

    // Hàm get cho config_key
    public function getConfigKey()
    {
        return $this->config_key;
    }

    // Hàm set cho config_content_en
    public function setConfigContentEn($config_content_en)
    {
        $this->config_content_en = $config_content_en;
    }

    // Hàm get cho config_content_en
    public function getConfigContentEn()
    {
        return $this->config_content_en;
    }

    // Hàm set cho config_content_vi
    public function setConfigContentVi($config_content_vi)
    {
        $this->config_content_vi = $config_content_vi;
    }

    // Hàm get cho config_content_vi
    public function getConfigContentVi()
    {
        return $this->config_content_vi;
    }

    // Hàm set cho config_crawl_en
    public function setConfigCrawlEn($config_crawl_en)
    {
        $this->config_crawl_en = $config_crawl_en;
    }

    // Hàm get cho config_crawl_en
    public function getConfigCrawlEn()
    {
        return $this->config_crawl_en;
    }

    // Hàm set cho config_crawl_vi
    public function setConfigCrawlVi($config_crawl_vi)
    {
        $this->config_crawl_vi = $config_crawl_vi;
    }

    // Hàm get cho config_crawl_vi
    public function getConfigCrawlVi()
    {
        return $this->config_crawl_vi;
    }
    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ScCountry[]|ScCountry
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ScCountry
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }


}
