<?php

namespace Score\Backend\Controllers;

use Score\Repositories\ConfigContent;
use Score\Models\ScConfig;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Score\Repositories\Language;


class ConfigcontentController extends ControllerBase
{

    public function indexAction()
    {
        $current_page = $this->request->getQuery('page', 'int');
        $sql = ScConfig::query()->where(" 1 ");
        $keyword = trim($this->request->get("txtSearch"));
        $lang = $this->request->get("slcLang", array('string', 'trim'));
        $langCode = $this->globalVariable->defaultLanguage;
        if (!empty($lang)) {
            $langCode = $lang;
            $this->dispatcher->setParam("slcLang", $langCode);
        }
        if ($keyword) {
            $sql->andWhere("config_key like CONCAT('%',:keyword:,'%')) OR  (config_content like CONCAT('%',:keyword:,'%')",["keyword" => $keyword]);
            $this->dispatcher->setParam("txtSearch", $keyword);
        }
        $sql->andWhere("config_language = :CODE:",["CODE" => $langCode]);
        $sql->orderBy("config_key ASC");
        $list_config = $sql->execute();
        $paginator = new PaginatorModel(array(
            'data' => $list_config,
            'limit' => 20,
            'page' => $current_page,
        ));

        $this->view->select_lang = Language::getCombo($langCode);
        $this->view->list_config = $paginator->getPaginate();
        if ($this->session->has('msg_result')) {
            $msg_result = $this->session->get('msg_result');
            $this->session->remove('msg_result');
            $this->view->msg_result = $msg_result;
        }
        if ($this->session->has('msg_del')) {
            $msg_result = $this->session->get('msg_del');
            $this->session->remove('msg_del');
            $this->view->msg_del = $msg_result;
        }
    }

    public function createAction()
    {
        $lang_default = $this->globalVariable->defaultLanguage;
        $arr_language = Language::arrLanguages();
        $arr_translate = array();
        $config_key = '';
        $messages = array();
        if ($this->request->isPost()) {
            foreach ($arr_language as $code => $lang) {
                $arr_translate[$code] = trim($this->request->getPost('content_' . $code));
            }
            $config_key = $this->request->getPost('txtKey', array('string', 'trim'));
            if (empty($config_key)) {
                $messages['key'] = 'Key field is required.';
            } else {
                if (ConfigContent::findByID($config_key)) {
                    $messages['key'] = 'Key field is exist.';
                }
                $pos = strpos($config_key, ' ');
                if ($pos !== false) {
                    $messages['key'] = 'Key have white space.';
                }
            }
            if (count($messages) === 0) {
                $url_delete_cache = defined('URL_DELETE_CACHE')?URL_DELETE_CACHE:'';
                if($url_delete_cache) {
                    $result = ConfigContent::curl_get_contents($url_delete_cache);
                }
                ConfigContent::deleteCache();
                $isCheck = true;
                foreach ($arr_language as $code => $lang) {
                    $config_model = ConfigContent::findByLanguage($config_key, $code);
                    if (!$config_model) {
                        $config_model = new ScConfig();
                        $config_model->setConfigKey($config_key);
                        $config_model->setConfigLanguage($code);
                    } else {
                        $old_data[] = $config_model->toArray();
                    }
                    $config_model->setConfigContent($arr_translate[$code]);
                    if (!$config_model->save()) $isCheck = false;
                }
                if ($isCheck) {
                    $msg_result = array('status' => 'success', 'msg' => 'Create Config Success !');
                } else {
                    $msg_result = array('status' => 'success', 'msg' => 'Create Config Fail !');
                }
                $this->session->set('msg_result', $msg_result);
                return $this->response->redirect('/dashboard/list-configcontent');
            }
        }
        $messages['status'] = 'border-red';
        $formData = array(
            'arr_translate' => $arr_translate,
            'arr_language' => $arr_language,
            'lang_default' => $lang_default,
            'messages' => $messages,
            'config_key' => $config_key,
            'mode' => 'create',
        );
        $this->view->formData = $formData;
    }

    public function editAction()
    {
        $config_key = trim($this->request->get("id"));
        $lang_default = $this->globalVariable->defaultLanguage;
        $check_find = ConfigContent::findByLanguage($config_key, $lang_default);
        if (!$check_find) {
            return $this->response->redirect("/notfound");
        }
        if ($this->session->has('msg_result')) {
            $msg_result = $this->session->get('msg_result');
            $this->session->remove('msg_result');
            $this->view->msg_result = $msg_result;
        }
        $messages = array();
        $arr_language = Language::arrLanguages();
        $arr_translate = array();
        $list_config = ConfigContent::getByID($config_key);
        foreach ($list_config as $config) {
            $arr_translate[$config->getConfigLanguage()] = $config->getConfigContent();
        }
        if ($this->request->isPost()) {
            $config_key = $this->request->getPost('txtKey', array('string', 'trim'));
            $arr_translate = array();
            foreach ($arr_language as $code => $lang) {
                $arr_translate[$code] = trim($this->request->get('content_' . $code));
            }
            if (empty($config_key)) {
                $messages['key'] = 'Keyword field is required.';
            }
            if (count($messages) === 0) {
                $url_delete_cache = defined('URL_DELETE_CACHE')?URL_DELETE_CACHE:'';
                if($url_delete_cache) {
                    $result = ConfigContent::curl_get_contents($url_delete_cache);
                }
                ConfigContent::deleteCache();
                $isCheck = true;
                foreach ($arr_language as $code => $lang) {
                    $config_model = ConfigContent::findByLanguage($config_key, $code);
                    if (!$config_model) {
                        $config_model = new ScConfig();
                        $config_model->setConfigKey($config_key);
                        $config_model->setConfigLanguage($code);
                    }
                    $config_model->setConfigContent($arr_translate[$code]);
                    if (!$config_model->save()) $isCheck = false;
                }
                if ($isCheck) {
                    $msg_result = array('status' => 'success', 'msg' => 'Update Config Success !');
                } else {
                    $msg_result = array('status' => 'success', 'msg' => 'Update Config Fail !');
                }
                $this->session->set('msg_result', $msg_result);
                return $this->response->redirect('/dashboard/edit-configcontent?id=' . $config_key);
            }

        }
        $messages['status'] = 'border-red';
        $formData = array(
            'arr_translate' => $arr_translate,
            'arr_language' => $arr_language,
            'lang_default' => $lang_default,
            'messages' => $messages,
            'config_key' => $config_key,
            'mode' => 'edit',
        );
        $this->view->formData = $formData;
    }

    public function deleteAction()
    {
        $config_key = trim($this->request->get("id"));
        $check_find = ConfigContent::findByID($config_key);
        if (!$check_find) {
            return $this->response->redirect("/notfound");
        }
        $url_delete_cache = defined('URL_DELETE_CACHE')?URL_DELETE_CACHE:'';
        if($url_delete_cache) {
            $result = ConfigContent::curl_get_contents($url_delete_cache);
        }
        $check_delete = ConfigContent::deletedByKey($config_key);
        if ($check_delete) {
            $msg_result = array('status' => 'success', 'msg' => 'Delete Config Key = ' . $config_key . '  Success !');
        } else {
            $msg_result = array('status' => 'success', 'msg' => 'Delete Config Key = ' . $config_key . '  Fail !');
        }
        $this->session->set('msg_result', $msg_result);
        return $this->response->redirect('/configcontent');
    }
}