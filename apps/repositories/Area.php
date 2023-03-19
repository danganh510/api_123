<?php

namespace Score\Repositories;

use Score\Models\ScArea;
use Phalcon\Mvc\User\Component;
use Phalcon\Di;

class Area extends Component {
    public static function findFirstById($id) {
        return ScArea::findFirst(array(
            'area_id = :id:',
            'bind' => array('id' => $id,)
        ));
    }
    public function checkName($area_name, $area_id)
    {
        return ScArea::findFirst(array(
            'area_name=:name: AND area_id != :id:',
            'bind' => array('name' => $area_name,
                            'id' => $area_id,)
        ));
    }
    public static function getNameById($id) {
        $model = self::findFirstById($id);
        return $model ? $model->getAreaName() : "";
    }
    public static function getAreaCombobox($id)
    {
        $country = ScArea::find("area_active = 'Y'");
        $output = '';
        foreach ($country as $value)
        {
            $selected = '';
            if(strtolower($value->getAreaId()) == $id)
            {
                $selected = 'selected';
            }
            $output.= "<option ".$selected." value='".strtolower($value->getAreaId())."'>".$value->getAreaName()."</option>";

        }
        return $output;
    }
}
