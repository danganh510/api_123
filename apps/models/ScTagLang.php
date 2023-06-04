<?php

namespace Score\Models;

class ScTagLang extends \Phalcon\Mvc\Model
{

    protected $tag_id;
    protected $tag_lang_code;
    protected $tag_name;



    /**
     * @return mixed
     */
    public function getTagId()
    {
        return $this->tag_id;
    }

    /**
     * @param mixed $tag_id
     */
    public function setTagId($tag_id)
    {
        $this->tag_id = $tag_id;
    }

    /**
     * @return mixed
     */
    public function getTagName()
    {
        return $this->tag_name;
    }
   /**
     * @param mixed $tag_lang_code
     */
    public function setTagLangCode($tag_lang_code)
    {
        $this->tag_lang_code = $tag_lang_code;
    }
       /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    /**
     * @param mixed $tag_name
     */
    public function setTagName($tag_name)
    {
        $this->tag_name = $tag_name;
    }
       /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'sc_tag_lang';
    }
    /**
     * Allows to query a set of records that Team the specified conditions
     *
     * @param mixed $parameters
     * @return ScTagLang[]|ScTagLang
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that Team the specified conditions
     *
     * @param mixed $parameters
     * @return ScTagLang
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }
 
}