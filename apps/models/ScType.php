<?php

namespace Score\Models;

class ScType extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=10, nullable=false)
     */
    protected $type_id;


    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    protected $type_parent_id;

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
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    protected $type_meta_image;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    protected $type_order;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $type_active;

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
     * Method to set the value of field type_parent_id
     *
     * @param integer $type_parent_id
     * @return $this
     */
    public function setTypeParentId($type_parent_id)
    {
        $this->type_parent_id = $type_parent_id;

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
     * Method to set the value of field type_meta_image
     *
     * @param string $type_meta_image
     * @return $this
     */
    public function setTypeMetaImage($type_meta_image)
    {
        $this->type_meta_image = $type_meta_image;

        return $this;
    }

    /**
     * Method to set the value of field type_order
     *
     * @param integer $type_order
     * @return $this
     */
    public function setTypeOrder($type_order)
    {
        $this->type_order = $type_order;

        return $this;
    }

    /**
     * Method to set the value of field type_active
     *
     * @param string $type_active
     * @return $this
     */
    public function setTypeActive($type_active)
    {
        $this->type_active = $type_active;

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
     * Returns the value of field type_parent_id
     *
     * @return integer
     */
    public function getTypeParentId()
    {
        return $this->type_parent_id;
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
     * Returns the value of field type_meta_image
     *
     * @return string
     */
    public function getTypeMetaImage()
    {
        return $this->type_meta_image;
    }

    /**
     * Returns the value of field type_order
     *
     * @return integer
     */
    public function getTypeOrder()
    {
        return $this->type_order;
    }

    /**
     * Returns the value of field type_active
     *
     * @return string
     */
    public function getTypeActive()
    {
        return $this->type_active;
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
        return 'sc_type';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ScType[]|ScType
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ScType
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
