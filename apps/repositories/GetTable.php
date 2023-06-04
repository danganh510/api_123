<?php

namespace Score\Repositories;

use Exception;
use Score\Models\ScLanguage;
use Phalcon\Mvc\User\Component;

class GetTable extends Component
{
    const TABLE_CAN_GET = ['sc_article', 'sc_banner', 'sc_type',"sc_page","sc_tag","sc_country","sc_area"];
    public function getColumnsModel($table)
    {

        $modelName = str_replace("_", " ", $table);

        $modelName = ucwords($modelName);
        $modelName = str_replace(" ", "", $modelName);
        $strModel = '\Score\Models\\' . $modelName;
        try {
            $model = new $strModel();
        } catch (Exception $e) {
            return false;
        }
        $column_model = $model->getModelsMetaData()->getAttributes($model);
        $column_id = reset($column_model);

        //lang
        $str_model_lang = $strModel . "Lang";
        try {
            $model_lang = new $str_model_lang();
        } catch (Exception $e) {
            return false;
        }
        $column_model_lang = $model_lang->getModelsMetaData()->getAttributes($model_lang);
        $column_lang_code = "";

        foreach ($column_model_lang as $column) {
            if (strpos($column, '_lang_code') || $column == "lang_code") {
                $column_lang_code = $column;
            }
        }
        return [
            'str' => $strModel,
            'strLang' => $str_model_lang,
            'column_id' => $column_id,
            'column_model' => $column_model,
            'column_model_lang' => $column_model_lang,
            'column_lang_code' => $column_lang_code
        ];
    }
    public function getListTable($params, $modelInfo)
    {
        $orderBy = $params['orderBy'];
        $list_data = [];

        $columns_get = [];
        foreach ($params['columns'] as $key => $column) {

            if (in_array($column, $columns_get)) {
                continue;
            }
            if (in_array($column, $modelInfo['column_model']) || $column == $modelInfo['column_id']) {
                $columns_get[] = "nlang." . $column;
                continue;
            }
        }
        $sql = $this->modelsManager->createBuilder()
            ->columns(implode(",", $columns_get))
            ->addFrom($modelInfo['str'], "nlang");
        if (isset($params['conditions']) && !empty($params['conditions'])) {
            $sql = $sql->where($params['conditions']);
        }
        $total = count($sql->getQuery()->execute());
        if (isset($params['limit'])) {
            $sql = $sql->limit($params['limit']);
        }
        if (isset($params['offset'])) {
            $sql = $sql->offset($params['offset'] * $params['limit']);
        }
        $sql = $sql->orderBy("nlang.{$orderBy} DESC");
        $list_data = $sql->getQuery()->execute();
        return [
            'list_data' => $list_data->toArray(),
            'total' => $total
        ];
    }
    public function getListTableLang($params, $modelInfo)
    {
        $orderBy = $params['orderBy'];
        $list_data = [];

        $column_only_model = array_diff($modelInfo['column_model'], $modelInfo['column_model_lang']);

        $columns_get = [];
        foreach ($params['columns'] as $key => $column) {

            if (in_array($column, $columns_get)) {
                continue;
            }
            if (in_array($column, $column_only_model) || $column == $modelInfo['column_id']) {
                $columns_get[] = "nlang." . $column;
                continue;
            }
            if (in_array($column, $modelInfo['column_model_lang'])) {
                $columns_get[] = "lang." . $column;
            }
        }
        $sql = $this->modelsManager->createBuilder()->columns(implode(",", $columns_get))
            ->addFrom($modelInfo['str'], "nlang")
            ->innerJoin($modelInfo['strLang'], "nlang.{$modelInfo['column_id']} = lang.{$modelInfo['column_id']}", 'lang')
            ->where("lang.{$modelInfo['column_lang_code']} = :lang_code:", ['lang_code' => $params['language']]);
        if (isset($modelInfo['conditions']) && !empty($modelInfo['conditions'])) {
            $sql = $sql->andWhere($modelInfo['conditions']);
        }
        $total = count($sql->getQuery()->execute());

        if (isset($params['limit'])) {
            $sql = $sql->limit($params['limit']);
        }
        if (isset($params['offset'])) {
            $sql = $sql->offset($params['offset'] * $params['limit']);
        }
        $sql = $sql->orderBy("nlang.{$orderBy} DESC");
        $list_data = $sql->getQuery()->execute();
        return [
            'list_data' => $list_data->toArray(),
            'total' => $total
        ];
    }
}