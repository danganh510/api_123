<?php

namespace Score\Repositories;

use Phalcon\Mvc\User\Component;
use Score\Models\ScAreaLang;

class AreaLang extends Component
{
        public static  function deleteById($id){
            $arr_lang = self::findById($id);
            foreach ($arr_lang as $lang){
                $lang->delete();
            }
        }
        public static  function findFirstByIdAndLang($id,$lang_code){
            return ScAreaLang::findFirst(array (
                "area_id = :ID: AND area_lang_code = :CODE:",
                'bind' => array('ID' => $id,
                                'CODE' => $lang_code )));
        }
        public static function findById($id) {
            return ScAreaLang::find([
                'area_id = :ID:',
                'bind' => array('ID' => $id),
            ]);
        }
}



