<?php

namespace Score\Api\Controllers;

use Exception;
use Score\Models\ScMatch;
use Score\Models\ScTeam;
use Score\Models\ScTournament;
use Score\Models\ScType;
use Score\Repositories\Article;
use Score\Repositories\Banner;
use Score\Repositories\CacheGetData;
use Score\Repositories\CacheMatch;
use Score\Repositories\CacheMatchLive;
use Score\Repositories\CacheTeam;
use Score\Repositories\CacheTour;
use Score\Repositories\Career;
use Score\Repositories\GetTable;
use Score\Repositories\MatchRepo;
use Score\Repositories\Page;
use Score\Repositories\Team;

class GetdataController extends ControllerBase
{
    public function listAction()
    {
        // $requestParams = [
        //     'columns' => ['type_id'],
        //     'language' => 'vi',
        //     'orderBy' => "type_id",
        //     'limit' => 1,
        //     'offset' => 1,
        //     'conditions' => "type_parent_id = 1"
        // ];

        $table = $this->dispatcher->getParam('table');
        if (!$table || !in_array($table, GetTable::TABLE_CAN_GET)) {
            $messages = "Table $table Not found ";
            $dataReturn = [
                'code' => 1000,
                'status' => false,
                'messages' => $messages
            ];
            goto end;
        }

        $paramsRequired = ["columns", "language", "orderBy"];
        $is_valid = $this->checkDataValid($paramsRequired, $this->requestParams, $dataReturn);
        if (!$is_valid)
            goto end;
        $getData = new GetTable();
        $modelInfo = $getData->getColumnsModel($table);
        if (!$modelInfo) {
            $messages = "Table $table Not found ";
            $dataReturn = [
                'code' => 1000,
                'status' => false,
                'messages' => $messages
            ];
            goto end;
        }
        foreach ($this->requestParams['columns'] as $column) {
            if (!in_array($column, $modelInfo['column_model'])) {
                $messages = "Column $column Not found in table";
                $dataReturn = [
                    'code' => 1000,
                    'status' => false,
                    'messages' => $messages
                ];
                goto end;
            }
        }
        // $cacheData = new CacheGetData($this->requestParams);
        // $result = $cacheData->getCache();
        // $result = [];
        // if (empty($result)) {
        //     if ($this->requestParams['language'] == $this->globalVariable->defaultLanguage) {
        //         $result = $getData->getListTable($this->requestParams, $modelInfo);
        //     } else {
        //         $result = $getData->getListTableLang($this->requestParams, $modelInfo);
        //     }
        //  //   $cacheData->setCache($result);
        // }
        // if ($this->requestParams['language'] == $this->globalVariable->defaultLanguage) {
        //     $result = $getData->getListTable($this->requestParams, $modelInfo);
        // } else {
        //     $result = $getData->getListTableLang($this->requestParams, $modelInfo);
        // }
        if ($this->requestParams['language'] == $this->globalVariable->defaultLanguage) {
            $result = $getData->getListTable($this->requestParams, $modelInfo);
        } else {
            $result = $getData->getListTableLang($this->requestParams, $modelInfo);
        }
        $dataReturn = [
            'code' => 200,
            'status' => true,
            'data' => $result['list_data'],
            'total' => $result['total'],
        ];
        end:

        return $dataReturn;
        //get match and tournament

    }
    public function detailAction()
    {
        // $requestParams = [
        //     'columns' => ['type_id'],
        //     'language' => 'vi',
        //     'orderBy' => "type_id",
        //     'conditions' => "type_parent_id = 1"
        // ];

        $table = $this->dispatcher->getParam('table');

        $paramsRequired = ["columns", "language", "orderBy"];
        $is_valid = $this->checkDataValid($paramsRequired, $this->requestParams, $dataReturn);
        if (!$is_valid)
            goto end;

        $getData = new GetTable();
        $modelInfo = $getData->getColumnsModel($table);
        foreach ($this->requestParams['columns'] as $column) {
            if (!in_array($column, $modelInfo['column_model'])) {
                $messages = "Column $column Not found in table";
                $dataReturn = [
                    'code' => 1000,
                    'status' => false,
                    'messages' => $messages
                ];
                goto end;
            }
        }

        if ($this->requestParams['language'] == $this->globalVariable->defaultLanguage) {
            $list_data = $getData->getListTable($this->requestParams, $modelInfo);
        } else {
            $list_data = $getData->getListTableLang($this->requestParams, $modelInfo);
        }
        $dataReturn = [
            'code' => 200,
            'status' => true,
            'data' => $list_data
        ];
        end:

        return $dataReturn;
        //get match and tournament

    }
}