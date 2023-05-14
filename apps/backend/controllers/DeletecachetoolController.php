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
        if ($this->request->isPost()) {
            $urlDelete =  "http://123tyso.live";
            $URL_DELETE_CACHE_TOOL = $urlDelete.'/delete-cache-data';
            $result = self::curl_get_contents($URL_DELETE_CACHE_TOOL);
            $this->session->set('msg_result', $result);
            $this->response->redirect("/dashboard/list-deletecachetool");
        }
    }
  

    function curl_get_contents($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
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