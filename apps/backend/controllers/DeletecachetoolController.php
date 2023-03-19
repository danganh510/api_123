<?php

namespace Score\Backend\Controllers;

class DeletecachetoolController extends ControllerBase
{
    const URL_DELETE_CACHE = 'configCache';
    const URL_DELETE_LOCATION_LANGUAGE_CACHE = 'locationLanguageCache';
    const URL_DELETE_ALL_CACHE = 'allCache';
    const URL_DELETE_ROUTER_CACHE = 'routerCache';
    const URL_DELETE_POPULAR_LOCATIONS = 'popularlocations';

    public function indexAction()
    {
        $msg_result = array();
        if ($this->session->has('msg_result')) {
            $msg_result = $this->session->get('msg_result');
            $this->session->remove('msg_result');
        }
        $this->view->setVars([
            'msg_result'=> $msg_result,
            'URL_DELETE_CACHE' => self::URL_DELETE_CACHE,
            'URL_DELETE_LOCATION_LANGUAGE_CACHE' => self::URL_DELETE_LOCATION_LANGUAGE_CACHE,
            'URL_DELETE_ALL_CACHE' => self::URL_DELETE_ALL_CACHE,
            'URL_DELETE_ROUTER_CACHE' => self::URL_DELETE_ROUTER_CACHE,
            'URL_DELETE_POPULAR_LOCATIONS' => self::URL_DELETE_POPULAR_LOCATIONS,
        ]);
    }

    public function deletecacheAction()
    {
        $this->view->disable();
        $btn_delete = $this->request->getPost('delete');
        $btn_delete_table = $this->request->getPost('deleteTable');
        $btn_delete_id = $this->request->getPost('deleteId');
        if ($this->request->isPost()) {
            $urlDelete =  defined('TEST_MODE') && TEST_MODE ? "https://sandbox.travelner.com" : "https://www.travelner.com";
            if ($btn_delete == self::URL_DELETE_POPULAR_LOCATIONS) {
                $URL_DELETE_CACHE_TOOL = $urlDelete.'/delete-cache-for-api?type=' . self::URL_DELETE_POPULAR_LOCATIONS;
            } else {
                $URL_DELETE_CACHE_TOOL = $urlDelete.'/delete-cache';
                $URL_DELETE_CACHE_TOOL .= '?type=' . $btn_delete;
                if (isset($btn_delete_table) && strlen($btn_delete_table) > 0){
                    $URL_DELETE_CACHE_TOOL .= '&table=' . $btn_delete_table;
                }
                if (isset($btn_delete_id) && strlen($btn_delete_id) > 0){
                    $URL_DELETE_CACHE_TOOL .= '&id=' . $btn_delete_id;
                }
            }
            $result = self::curl_get_contents($URL_DELETE_CACHE_TOOL);
            $this->session->set('msg_result', $result);
            $this->response->redirect("/deletecachetool");
        }
    }
    public function deletecachetypeAction()
    {
        $this->view->disable();
        if ($this->request->isPost()) {
            $time = $this->globalVariable->curTime;
            $type = $this->request->getPost('delete');
            if ($type == "airports") {
                unlink(__DIR__."/../../messages/caches/airport.txt");
            }
            $paramStr = '{"iat":'.$time.',"type":"'.$type.'"}';
            $repoMy = new \My();
            $obj = $repoMy->getDataResultApi($paramStr,'/frontend/deletecache/type',(defined('TEST_MODE') && TEST_MODE));

            if (isset($obj->isSuccessful) && $obj->isSuccessful=="true") {
                if ($obj->data) {
                    $msg_result = [
                        'status' => $obj->data->status,
                        'message' => $obj->data->message,
                    ];
                } else {
                    $msg_result =  [
                        'status' => 'false',
                        'message' => 'Delete cache Fail',
                    ];
                }
            }  else {
                $mesages = isset($obj->message) && $obj->message != "" ? $obj->message : "Connect to API fail";
                $msg_result =  [
                    'status' => 'false',
                    'message' =>$mesages,
                ];
            }

            $this->session->set('msg_result', $msg_result);
            return $this->response->redirect("/deletecachetool");

        }
    }

    function curl_get_contents($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            "ctoken=k3FRQ1U0bYHUVSu6");
        $data = curl_exec($ch);
        curl_close($ch);
        $data_de = json_decode($data,true);
        if($data_de === NULL){
            $data_de= [
                'status'=>'error',
                'message' =>$data." false",
            ];
        }
        return $data_de;
    }
}