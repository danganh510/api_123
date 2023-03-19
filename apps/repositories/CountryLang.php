<?php

namespace Score\Repositories;

use Phalcon\Mvc\User\Component;
use Score\Models\ScCountryLang;

class CountryLang extends Component
{
    public static function deleteById($id)
    {
        $arr_lang = ScCountryLang::findById($id);
        foreach ($arr_lang as $lang) {
            $lang->delete();
        }
    }
    public static function findFirstByIdAndLang($id, $lang_code)
    {
        return ScCountryLang::findFirst(array(
            "country_id = :ID: AND country_lang_code = :CODE:",
            'bind' => array('ID' => $id,
                'CODE' => $lang_code)));
    }    
}