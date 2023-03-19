<?php

namespace Score\Repositories;

use Phalcon\Mvc\User\Component;
use Score\Models\ScPageLang;

class PageLang extends Component
{
    public static function deleteById($id)
    {
        self::findById($id)->delete();
    }

    public static function deleteByIdAndLocationCountryCode($id, $country_code)
    {
        self::findByIdAndLocationCountryCode($id, $country_code)->delete();
    }

    public static function findFirstByIdAndLang($id, $lang_code)
    {
        return ScPageLang::findFirst(array(
            "page_id = :ID:  AND page_lang_code = :CODE:",
            'bind' => array('ID' => $id,
                'CODE' => $lang_code)));
    }
    public static function findByIdAndLocationCountryCode($id, $country_code)
    {
        return ScPageLang::find(array(
            "page_id =:ID: AND page_location_country_code = :country_code:",
            'bind' => array('ID' => $id, 'country_code' => $country_code)
        ));
    }
    public static function findById($id)
    {
        return ScPageLang::find(array(
            "page_id =:ID:",
            'bind' => array('ID' => $id)
        ));
    }
}



