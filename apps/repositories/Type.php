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
use Score\Models\ScType;

class Type extends Component
{
    public static function checkKeyword($keyword, $id)
    {
        return ScType::findFirst(array(
                'type_keyword = :keyword: AND type_id != :id: ',
                'bind' => array('keyword' => $keyword,
                    'id' => $id),
            )
        );
    }

    public static function getParentIdType($str = "", $parent=0, $inputslc)
    {
        $modelsManager = Di::getDefault()->get('modelsManager');
        $sql = 'SELECT type_id ,type_name FROM Score\Models\ScType WHERE type_parent_id = :parentID: Order By type_order ASC';
        $data = $modelsManager->executeQuery($sql,
            array(
                "parentID" => $parent
            ));
        $output="";
        if (!is_array($inputslc)){
            $inputslc = [$inputslc];
        }
        foreach ($data as $key => $value)
        {
            $selected ="";
            if (in_array($value->type_id,$inputslc)) {
                $selected = "selected='selected'";
            }
            $output.= "<option ".$selected." value='".$value->type_id."'>".$str.$value->type_name."</option>";
            $output.= self::getParentIdType($str."----", $value->type_id, $inputslc);

        }
        return $output;
    }

    public static function getTypeNameById($id){
        $model = self::findFirstById($id);
        return $model ? $model->getTypeName() : '';

    }
    public static function findFirstById($id)
    {
        return ScType::findFirst(array(
            "type_id =:ID:",
            'bind' => array('ID' => $id)
        ));
    }
    public static function findById($id)
    {
        return ScType::find(array(
            'type_id = :id:',
            'bind' => array('id' => $id),
        ));
    }
    public static function findFirstByParentId($typeId)
    {
        return ScType::findFirst(array(
            "type_parent_id = :ID:",
            'bind' => array('ID' => $typeId)
        ));
    }
}