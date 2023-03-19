<?php

namespace Score\Repositories;

use Phalcon\Mvc\User\Component;
use Score\Models\ScArticleLang;

class ArticleLang extends Component
{
    public static function deleteById($id)
    {
        self::findById($id)->delete();
    }

    public static function findFirstByIdAndLang($id, $lang_code)
    {
        return ScArticleLang::findFirst(array(
            "article_id = :ID:  AND article_lang_code = :CODE:",
            'bind' => array('ID' => $id,
                'CODE' => $lang_code)));
    }

    public static function findById($id)
    {
        return ScArticleLang::find(array(
            "article_id =:ID:",
            'bind' => array('ID' => $id)
        ));
    }
    public static function findFirstById($id) {
        return ScArticleLang::findFirst([
            "article_id =:ID:",
            'bind' => array('ID' => $id)
        ]);
    }
}



