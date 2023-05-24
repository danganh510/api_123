<?php

/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 3/9/2021
 * Time: 5:04 PM
 */

namespace Score\Repositories;

use Phalcon\Di;
use Phalcon\Mvc\User\Component;
use Score\Models\ScArticle;
use Score\Models\ScTag;
use Score\Models\ScType;

class Article extends Component
{
    public static function selectTag($arTagSelect)
    {
        $arTag = ScTag::find();
        $output = "";
        foreach ($arTag as $key => $value) {
            $selected = "";
            if (in_array($value->tag_name, $arTagSelect)) {
                $selected = "selected='selected'";
            }
            $output .= "<option " . $selected . " value='" . $value->tag_name . "'>" .  $value->tag_name. "</option>";

        }
        return $output;
    }
    public static function checkKeyword($keyword, $id)
    {
        return ScArticle::findFirst(
            array(
                'article_keyword = :keyword: AND article_id != :id: ',
                'bind' => array(
                    'keyword' => $keyword,
                    'id' => $id
                ),
            )
        );
    }
    public static function findFirstByType($type)
    {
        return ScArticle::findFirst([
            'article_type_id = :type:',
            'bind' => ['type' => $type]
        ]);
    }
    public static function findById($id)
    {
        return ScArticle::find(array(
            'article_id = :id:',
            'bind' => array('id' => $id),
        ));
    }
    public static function findFirstById($id)
    {
        return ScArticle::findFirst(array(
            'article_id = :id:',
            'bind' => array('id' => $id),
        ));
    }
}
