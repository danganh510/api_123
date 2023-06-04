<?php

namespace Score\Models;

class ScTag extends \Phalcon\Mvc\Model
{

    protected $tag_id;
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
        return 'sc_tag';
    }
    /**
     * Allows to query a set of records that Team the specified conditions
     *
     * @param mixed $parameters
     * @return ScTag[]|ScTag
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that Team the specified conditions
     *
     * @param mixed $parameters
     * @return ScTag
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }
    public static function findTagByName($name)
    {
        return self::findFirst([
            'tag_name = :name:',
            'bind' => ['name' => $name]
        ]);
    }
    public static function findTagNameById($id)
    {
        $model =  self::findFirst([
            'tag_id = :id:',
            'bind' => ['id' => $id]
        ]);
        return $model ? $model->getTagName() : "";
    }
}