<?php

namespace Score\Models;

class ScLanguage extends \Phalcon\Mvc\Model
{
    const GENERAL = 'general';
    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $language_id;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    protected $language_name;

    /**
     *
     * @var string
     * @Column(type="string", length=5, nullable=false)
     */
    protected $language_code;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    protected $language_code_time;

    /**
     *
     * @var string
     * @Column(type="string", length=5, nullable=false)
     */
    protected $language_country_code;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $language_order;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $language_active;
    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $language_is_translate_keyword;
    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $language_active_mobile;

    /**
     * Method to set the value of field language_id
     *
     * @param integer $language_id
     * @return $this
     */
    public function setLanguageId($language_id)
    {
        $this->language_id = $language_id;

        return $this;
    }

    /**
     * Method to set the value of field language_name
     *
     * @param string $language_name
     * @return $this
     */
    public function setLanguageName($language_name)
    {
        $this->language_name = $language_name;

        return $this;
    }

    /**
     * Method to set the value of field language_code
     *
     * @param string $language_code
     * @return $this
     */
    public function setLanguageCode($language_code)
    {
        $this->language_code = $language_code;

        return $this;
    }

    /**
     * Method to set the value of field language_code_time
     *
     * @param string $language_code_time
     * @return $this
     */
    public function setLanguageCodeTime($language_code_time)
    {
        $this->language_code_time = $language_code_time;

        return $this;
    }

    /**
     * Method to set the value of field language_country_code
     *
     * @param string $language_country_code
     * @return $this
     */
    public function setLanguageCountryCode($language_country_code)
    {
        $this->language_country_code = $language_country_code;

        return $this;
    }

    /**
     * Method to set the value of field language_order
     *
     * @param integer $language_order
     * @return $this
     */
    public function setLanguageOrder($language_order)
    {
        $this->language_order = $language_order;

        return $this;
    }

    /**
     * Method to set the value of field language_active
     *
     * @param string $language_active
     * @return $this
     */
    public function setLanguageActive($language_active)
    {
        $this->language_active = $language_active;

        return $this;
    }

    /**
     * Method to set the value of field language_active_mobile
     *
     * @param string $language_active_mobile
     * @return $this
     */
    public function setLanguageActiveMobile($language_active_mobile)
    {
        $this->language_active_mobile = $language_active_mobile;

        return $this;
    }
    /**
     * Method to set the value of field language_is_translate_keyword
     *
     * @param string $language_is_translate_keyword
     * @return $this
     */
    public function setLanguageIsTranslateKeyword($language_is_translate_keyword)
    {
        $this->language_is_translate_keyword  = $language_is_translate_keyword;

        return $this;
    }
    /**
     * Returns the value of field language_id
     *
     * @return integer
     */
    public function getLanguageId()
    {
        return $this->language_id;
    }

    /**
     * Returns the value of field language_name
     *
     * @return string
     */
    public function getLanguageName()
    {
        return $this->language_name;
    }

    /**
     * Returns the value of field language_code
     *
     * @return string
     */
    public function getLanguageCode()
    {
        return $this->language_code;
    }

    /**
     * Returns the value of field language_code_time
     *
     * @return string
     */
    public function getLanguageCodeTime()
    {
        return $this->language_code_time;
    }

    /**
     * Returns the value of field language_country_code
     *
     * @return string
     */
    public function getLanguageCountryCode()
    {
        return $this->language_country_code;
    }

    /**
     * Returns the value of field language_order
     *
     * @return integer
     */
    public function getLanguageOrder()
    {
        return $this->language_order;
    }

    /**
     * Returns the value of field language_active
     *
     * @return string
     */
    public function getLanguageActive()
    {
        return $this->language_active;
    }
    /**
     * Returns the value of field language_active_mobile
     *
     * @return string
     */
    public function getLanguageActiveMobile()
    {
        return $this->language_active_mobile;
    }
    /**
     * Returns the value of field language_is_translate_keyword
     *
     * @return string
     */
    public function getLanguageIsTranslateKeyword()
    {
        return $this->language_is_translate_keyword;
    }


    /**
     * Initialize method for model.
     */
//    public function initialize()
//    {
//        $this->setSchema("Scvncompanycorpcom_new");
//    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'sc_language';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ScLanguage[]|ScLanguage
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ScLanguage
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
