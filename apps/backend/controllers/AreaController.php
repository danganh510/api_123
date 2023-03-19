<?php

namespace Score\Backend\Controllers;

use Score\Models\ScArea;
use Score\Models\ScAreaLang;
use Score\Models\ScCountry;
use Score\Models\ScLanguage;
use Score\Repositories\Area;
use Score\Repositories\AreaLang;
use Score\Repositories\Country;
use Score\Repositories\Language;
use Score\Utils\Validator;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
class AreaController extends ControllerBase
{
    public function indexAction()
    {
        $current_page = $this->request->get('page');
        $validator = new Validator();
        if($validator->validInt($current_page) == false || $current_page < 1)
            $current_page=1;
        $keyword = trim($this->request->get("txtSearch"));
        $sql = "SELECT * FROM Score\Models\ScArea WHERE 1";
        $arrParameter = array();
        if(!empty($keyword)){
            if($validator->validInt($keyword)) {
                $sql.=" AND (area_id = :keyword:) ";
            } else {
                $sql.=" AND (area_name like CONCAT('%',:keyword:,'%'))";
            }
            $arrParameter['keyword'] = $keyword;
            $this->dispatcher->setParam("txtSearch", $keyword);
        }
        $sql.=" ORDER BY area_id DESC";
        $list_area = $this->modelsManager->executeQuery($sql,$arrParameter);
        $paginator = new PaginatorModel(
            [
                'data'  => $list_area,
                'limit' => 20,
                'page'  => $current_page,
            ]
        );
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

        $this->view->list_area = $paginator->getPaginate();
    }


    public function createAction()
    {
        $data = array('area_id' => -1,'area_active' => 'Y', 'area_order' => 1, );
        if($this->request->isPost()) {
            $messages = array();
            $data = array(
                'area_name' => $this->request->getPost("txtName", array('string', 'trim')),
                'area_lat' => $this->request->getPost("txtLat", array('trim')),
                'area_lng' => $this->request->getPost("txtLng", array('trim')),
                'area_order' => $this->request->getPost("txtOrder", array('string', 'trim')),
                'area_active' => $this->request->getPost("radActive"),
            );
            if (empty($data["area_name"])) {
                $messages["name"] = "Name field is required.";
            } else {
                $check_area_name = new Area();
                if($check_area_name->checkName($data['area_name'],-1)) {
                    $messages["name"] = "Name is exists.";
                }
            }
            if($data['area_lat'] == "") {
                $messages['lat'] = 'Latitude field is required.';
            }
//            -------------
            if($data['area_lng']== "") {
                $messages['lng'] = 'Longitude field is required.';
            }

            if (empty($data['area_order'])) {
                $messages["order"] = "Order field is required.";
            } else if (!is_numeric($data["area_order"])) {
                $messages["order"] = "Order is not valid ";
            }
            if (count($messages) == 0) {
                    $msg_result = array();
                    $new_area = new ScArea();
                    $new_area->setAreaName($data["area_name"]);
                    $new_area->setAreaLat($data["area_lat"]);
                    $new_area->setAreaLng($data["area_lng"]);
                    $new_area->setAreaOrder($data["area_order"]);
                    $new_area->setAreaActive($data["area_active"]);
                    $result = $new_area->save();
                    if ($result === true) {
                        $msg_result = array('status' => 'success', 'msg' => 'Create Area Success');

                    } else {
                        $message = "We can't store your info now: \n";
                        foreach ($new_area->getMessages() as $msg) {
                            $message .= $msg . "\n";
                        }
                        $msg_result['status'] = 'error';
                        $msg_result['msg'] = $message;
                    }
                    $this->session->set('msg_result', $msg_result);
                    return $this->response->redirect("/dashboard/list-area");
                }
        }
            $messages["status"] = "border-red";
            $this->view->setVars([
                'oldinput' => $data,
                'messages' => $messages,
            ]);
    }

    /**
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     */
    public function editAction()
    {
        $area_id = $this->request->get('id');
        $checkID = new Validator();
        if(!$checkID->validInt($area_id))
        {
            $this->response->redirect('notfound');
            return ;
        }
        $area_model = Area::findFirstById($area_id);
        if(empty($area_model))
        {
            $this->response->redirect('notfound');
            return;
        }
        $arr_translate = array();
        $messages = array();
        $data_post = array (
            'area_id' => $area_id,
            'area_name' => '',
            'area_lat' => '',
            'area_lng' => '',
            'area_order' => '',
            'area_active' => '',
        ) ;
        $save_mode = '';
        $lang_default = $this->globalVariable->defaultLanguage;
        $lang_current = $lang_default;
        $arr_language = Language::arrLanguages();
        if($this->request->isPost()) {
            if(!isset($_POST['save'])){
                $this->view->disable();
                $this->response->redirect("notfound");
                return;
            }
            $save_mode =  $_POST['save'] ;
            if (isset($arr_language[$save_mode])) {
                $lang_current = $save_mode;
            }
            if($save_mode != ScLanguage::GENERAL) {
                $data_post['area_name'] = $this->request->get("txtName", array('string', 'trim'));
                if (empty($data_post['area_name'])) {
                    $messages[$save_mode]['name'] = 'Name field is required.';
                } else {
                    $check_area_name = new Area();
                    if($check_area_name->checkName($data_post['area_name'],$data_post['area_id'])) {
                        $messages[$save_mode]["name"] = "Name is exist.";
                    }
                }
            } else {
                $data_post['area_lat'] = $this->request->getPost("txtLat", array('trim'));
                $data_post['area_lng'] = $this->request->getPost("txtLng", array('trim'));
                $data_post['area_order'] = $this->request->getPost("txtOrder", array('string', 'trim'));
                $data_post['area_active'] = $this->request->getPost("radActive");
                if($data_post['area_lat'] == "") {
                    $messages['lat'] = 'Latitude field is required.';
                }
                if($data_post['area_lng'] == "") {
                    $messages['lng'] = 'Longitude field is required.';
                }
                if (empty($data_post['area_order'])) {
                    $messages["order"] = "Order field is required.";
                } else if (!is_numeric($data_post["area_order"])) {
                    $messages["order"] = "Order is not valid ";
                }
            }
            if(empty($messages)) {
                switch ($save_mode) {
                    case ScLanguage::GENERAL:
                        $area_model->setAreaLat($data_post['area_lat']);
                        $area_model->setAreaLng($data_post['area_lng']);
                        $area_model->setAreaOrder($data_post['area_order']);
                        $area_model->setAreaActive($data_post['area_active']);
                        $result = $area_model->update();
                        $info = ScLanguage::GENERAL;

                        break;
                    case $this->globalVariable->defaultLanguage :
                        $area_model->setAreaName($data_post['area_name']);

                        $result = $area_model->update();
                        $info = $arr_language[$save_mode];

                        break;
                    default:
                        $area_lang_model = AreaLang::findFirstByIdAndLang($area_id, $save_mode);
                        if (!$area_lang_model) {
                            $area_lang_model = new ScAreaLang();
                            $area_lang_model->setAreaId($area_id);
                            $area_lang_model->setAreaLangCode($save_mode);
                        }
                        
                        $area_lang_model->setAreaName($data_post['area_name']);
                        $result = $area_lang_model->save();
                        $info = $arr_language[$save_mode];
                        break;
                }
                if ($result) {
                    $messages = array(
                        'message' => ucfirst($info . " Update Area success"),
                        'typeMessage' => "success",
                    );
                }else{
                    $messages = array(
                        'message' => "Update Area fail",
                        'typeMessage' => "error",
                    );
                }
            }
        }
        $item = array(
            'area_id' =>$area_model->getAreaId(),
            'area_name'=>($save_mode ===$this->globalVariable->defaultLanguage)?$data_post['area_name']:$area_model->getAreaName(),
        );
        $arr_translate[$lang_default] = $item;
        $arr_area_lang = AreaLang::findById($area_id);
        foreach ($arr_area_lang as $area_lang){
            $item = array(
                'area_id'=>$area_lang->getAreaId(),
                'area_name'=>($save_mode === $area_lang->getAreaLangCode())?$data_post['area_name']:$area_lang->getAreaName(),
            );
            $arr_translate[$area_lang->getAreaLangCode()] = $item;
        }
        if(!isset($arr_translate[$save_mode])&& isset($arr_language[$save_mode])){
            $item = array(
                'area_id'=> -1,
                'area_name'=> $data_post['area_name'],
            );
            $arr_translate[$save_mode] = $item;
        }
        $formData = array(
            'area_id'=>$area_model->getAreaId(),
            'area_lat' => ($save_mode ===ScLanguage::GENERAL)?$data_post['area_lat']:$area_model->getAreaLat(),
            'area_lng' => ($save_mode ===ScLanguage::GENERAL)?$data_post['area_lng']:$area_model->getAreaLng(),
            'area_order' => ($save_mode ===ScLanguage::GENERAL)?$data_post['area_order']:$area_model->getAreaOrder(),
            'area_active' => ($save_mode ===ScLanguage::GENERAL)?$data_post['area_active']:$area_model->getAreaActive(),
            'arr_translate' => $arr_translate,
            'arr_language' => $arr_language,
            'lang_default' => $lang_default,
            'lang_current' => $lang_current
        );
        $messages['status'] = 'border-red';
        $this->view->setVars([
            'formData' => $formData,
            'messages' => $messages,
        ]);
    }
    public function deleteAction()
    {
        $area_check = $this->request->getPost("item");
        if(!empty($area_check))
        {
            $messages = array('error'   => '',
                            'success' => '');
            $total = 0;
            foreach ($area_check as $area_id)
            {
                $area_item = ScArea::findFirst($area_id);
                if($area_item)
                {
                    $country = Country::findFirstByArea($area_id);
                    if($country)
                    {
                        $message = 'Can\'t delete the area Name = '.$area_item->getAreaName().'. Because It\'s exists in Country.<br>';
                        $messages['error'] .=$message ;
                    }
                    else {
                        if ($area_item->delete()) {
                            $total++;
                            AreaLang::deleteById($area_id);

                        }
                    }
                }
            }
            if($total > 0) {
                $messages['success'] = 'Delete '. $total .' area successfully.';
            }
            $this->session->set('msg_del', $messages);
            return $this->response->redirect("/dashboard/list-area");
        }
    }
}