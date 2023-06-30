<?php

namespace Score\Models;

class ScTypeLang extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=10, nullable=false)
     */
    protected $type_id;

    /**
     *
     * @var string
     * @Primary
     * @Column(type="string", length=5, nullable=false)
     */
    protected $type_lang_code;

    /**
     *
     * @var string
     * @Primary
     * @Column(type="string", length=5, nullable=false)
     */
    protected $type_location_country_code;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    protected $type_name;
    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    protected $type_keyword;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    protected $type_title;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    protected $type_meta_keyword;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    protected $type_meta_description;

    /**
     * Method to set the value of field type_id
     *
     * @param integer $type_id
     * @return $this
     */
    public function setTypeId($type_id)
    {
        $this->type_id = $type_id;

        return $this;
    }

    /**
     * Method to set the value of field type_lang_code
     *
     * @param string $type_lang_code
     * @return $this
     */
    public function setTypeLangCode($type_lang_code)
    {
        $this->type_lang_code = $type_lang_code;

        return $this;
    }

    /**
     * Method to set the value of field type_location_country_code
     *
     * @param string $type_location_country_code
     * @return $this
     */
    public function setTypeLocationCountryCode($type_location_country_code)
    {
        $this->type_location_country_code = $type_location_country_code;

        return $this;
    }

    /**
     * Method to set the value of field type_name
     *
     * @param string $type_name
     * @return $this
     */
    public function setTypeName($type_name)
    {
        $this->type_name = $type_name;

        return $this;
    }

    /**
     * Method to set the value of field type_keyword
     *
     * @param string $type_keyword
     * @return $this
     */
    public function setTypeKeyword($type_keyword)
    {
        $this->type_keyword = $type_keyword;

        return $this;
    }
    /**
     * Method to set the value of field type_title
     *
     * @param string $type_title
     * @return $this
     */
    public function setTypeTitle($type_title)
    {
        $this->type_title = $type_title;

        return $this;
    }

    /**
     * Method to set the value of field type_meta_keyword
     *
     * @param string $type_meta_keyword
     * @return $this
     */
    public function setTypeMetaKeyword($type_meta_keyword)
    {
        $this->type_meta_keyword = $type_meta_keyword;

        return $this;
    }

    /**
     * Method to set the value of field type_meta_description
     *
     * @param string $type_meta_description
     * @return $this
     */
    public function setTypeMetaDescription($type_meta_description)
    {
        $this->type_meta_description = $type_meta_description;

        return $this;
    }

    /**
     * Returns the value of field type_id
     *
     * @return integer
     */
    public function getTypeId()
    {
        return $this->type_id;
    }

    /**
     * Returns the value of field type_lang_code
     *
     * @return string
     */
    public function getTypeLangCode()
    {
        return $this->type_lang_code;
    }

    /**
     * Returns the value of field type_location_country_code
     *
     * @return string
     */
    public function getTypeLocationCountryCode()
    {
        return $this->type_location_country_code;
    }

    /**
     * Returns the value of field type_name
     *
     * @return string
     */
    public function getTypeName()
    {
        return $this->type_name;
    }
    /**
     * Returns the value of field type_keyword
     *
     * @return string
     */
    public function getTypeKeyword()
    {
        return $this->type_keyword;
    }
    /**
     * Returns the value of field type_title
     *
     * @return string
     */
    public function getTypeTitle()
    {
        return $this->type_title;
    }

    /**
     * Returns the value of field type_meta_keyword
     *
     * @return string
     */
    public function getTypeMetaKeyword()
    {
        return $this->type_meta_keyword;
    }

    /**
     * Returns the value of field type_meta_description
     *
     * @return string
     */
    public function getTypeMetaDescription()
    {
        return $this->type_meta_description;
    }

    /**
     * Initialize method for model.
     */
    //    public function initialize()
    //    {
    //        $this->setSchema("travelner");
    //    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'sc_type_lang';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ScTypeLang[]|ScTypeLang
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ScTypeLang
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }
}
