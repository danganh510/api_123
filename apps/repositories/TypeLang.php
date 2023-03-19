<?php

namespace Score\Repositories;

use Phalcon\Mvc\User\Component;
use Score\Models\ScTypeLang;

class TypeLang extends Component
{


    public static function deleteById($id)
    {
        self::findById($id)->delete();
    }

    public static function findFirstByIdeAndLang($id, $lang_code)
    {
        return ScTypeLang::findFirst(array(
            "type_id = :ID:  AND type_lang_code = :CODE:",
            'bind' => array('ID' => $id,
                'CODE' => $lang_code)));
    }
    public static function findFirstByIdAndLang($id, $lang_code)
    {

        return ScTypeLang::findFirst(array(
            " type_id = :ID: AND type_lang_code = :CODE: ",
            'bind' => array('ID' => $id,
                'CODE' => $lang_code)));
    }

    public static function findById($id)
    {
        return ScTypeLang::find(array(
            "type_id =:ID:",
            'bind' => array('ID' => $id)
        ));
    }
}



