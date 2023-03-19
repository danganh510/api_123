<?php

namespace Score\Backend\Controllers;

use Score\Models\ScCountry;
use Score\Models\ScCountryLang;
use Score\Repositories\Area;
use Score\Repositories\Country;
use Score\Repositories\CountryLang;
use Score\Repositories\Language;
use Score\Utils\Validator;
use Phalcon\Paginator\Adapter\NativeArray;

class CountryController extends ControllerBase
{
    public function indexAction()
    {

        $current_page = $this->request->getQuery('page');
        $keyword = $this->request->get('txtSearch', array('string', 'trim'));
        $lang = $this->request->get('slcLang', array('string', 'trim'));
        $langCode = !empty($lang) ? $lang : $this->globalVariable->defaultLanguage;
        $data = $this->getParameter($keyword, $langCode);
        $list_country =  $this->modelsManager->executeQuery($data['sql'], $data['para']);

        $result = array();
        if ($list_country && sizeof($list_country) > 0) {
            if ($langCode != $this->globalVariable->defaultLanguage) {
                foreach ($list_country as $item) {
                    $result[] = \Phalcon\Mvc\Model::cloneResult(
                        new ScCountry(), array_merge($item->p->toArray(), $item->pl->toArray()));
                }
            } else {
                foreach ($list_country as $item) {
                    $result[] = \Phalcon\Mvc\Model::cloneResult(new ScCountry(), $item->toArray());
                }
            }
        }
        $paginator = new NativeArray(
            [
                'data' => $result,
                'limit' => 300,
                'page' => $current_page,
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
        $select_lang = Language::getCombo($langCode);
        $this->view->setVars(array(
            'list_country' => $paginator->getPaginate(),
            'select_lang' => $select_lang,
        ));
    }

    public function createAction()
    {
        $data = array('country_id' => -1, 'country_active' => 'Y','country_order' => 1,'country_area_id' => '');
        if ($this->request->isPost()) {
            $data = array(
                'country_id' => -1,
                'country_code' => $this->request->getPost("txtCode", array('string', 'trim')),
                'country_area_id' => $this->request->getPost("slcArea", array('string', 'trim')),
                'country_name' => $this->request->getPost("txtName", array('string', 'trim')),
                'country_nationality' => $this->request->getPost("txtNationality"),
                'country_order' => $this->request->getPost("txtOrder", array('string', 'trim')),
                'country_phone_code' => $this->request->getPost("txtPhoneCode", array('string', 'trim')),
                'country_ads_label' => $this->request->getPost("txtAdsLabel", array('string', 'trim')),
                'country_active' => $this->request->getPost("radActive"),
                'country_support_insurance' => $this->request->getPost("radSupportInsurance"),

            );
            $messages = array();
            if (empty($data['country_code'])) {
                $messages['code'] = 'Code field is required.';
            }
            if (empty($data['country_name'])) {
                $messages['name'] = 'Name field is required.';
            }

            if (empty($data['country_order'])) {
                $messages['country_order'] = "Order field is required.";
            } else if (!is_numeric($data['country_order'])) {
                $messages['country_order'] = "Order is not valid";
            }

            if (count($messages) == 0) {
                $new_country = new ScCountry();
                $new_country->setCountryCode($data['country_code']);
                $new_country->setCountryName($data['country_name']);
                $new_country->setCountryNationality($data['country_nationality']);
                $new_country->setCountryOrder($data['country_order']);
                $new_country->setCountryAreaId($data['country_area_id']);
                $new_country->setCountryPhoneCode($data['country_order']);
                $new_country->setCountryAdsLabel($data['country_ads_label']);
                $new_country->setCountryActive($data['country_active']);
                $new_country->setCountrySupportInsurance($data['country_support_insurance']);
                if ($new_country->save() === true) {
                    $msg_result = array('status' => 'success', 'msg' => 'Create Country Success');
                } else {
                    $msg_result = array('status' => 'error', 'msg' => 'Create Country Fail !');
                }
                $this->session->set('msg_result', $msg_result);
                return $this->response->redirect('/dashboard/list-country');
            }
        }
        $messages['status'] = 'border-red';
        $select_area = Area::getAreaCombobox($data['country_area_id']);
        $this->view->setVars(array(
            'formData' => $data,
            'messages' => $messages,
            'select_area' => $select_area
        ));
    }

    public function editAction()
    {
        $id_country = $this->request->getQuery('id');
        $checkID = new Validator();
        if (!$checkID->validInt($id_country)) {
            return $this->response->redirect('notfound');
        }
        $country_model = Country::findFirstById($id_country);
        if (empty($country_model)) {
            return $this->response->redirect('notfound');
        }
        $arr_translate = array();
        $messages = array();
        $data_post = array(
            'country_name' => '',
            'country_code' => '',
            'country_nationality' => '',
            'country_order' => '',
            'country_ads_label' => '',
            'country_phone_code' => '',
            'country_active' => '',
        );
        $save_mode = '';
        $lang_default = $this->globalVariable->defaultLanguage;
        $arr_language = Language::arrLanguages();
        $lang_current = $lang_default;
        if ($this->request->isPost()) {
            if (!isset($_POST['save'])) {
                $this->view->disable();
                $this->response->redirect("notfound");
                return;
            }
            $save_mode = $_POST['save'];
            if (isset($arr_language[$save_mode])) {
                $lang_current = $save_mode;
            }
            if ($save_mode != 'general') {
                $data_post['country_name'] = $this->request->get("txtName", array('string', 'trim'));
                $data_post['country_nationality'] = $this->request->get("txtNationality");
                if (empty($data_post['country_name'])) {
                    $messages['name'] = 'Name field is required';
                }
            } else {
                $data_post['country_code'] = $this->request->get("txtCode", array('string', 'trim'));
                $data_post['country_area_id'] = $this->request->getPost("slcArea", array('string', 'trim'));
                $data_post['country_order'] = $this->request->get("txtOrder", array('string', 'trim'));
                $data_post['country_ads_label'] = $this->request->get("txtAdsLabel", array('string', 'trim'));
                $data_post['country_phone_code'] = $this->request->get("txtPhoneCode", array('string', 'trim'));
                $data_post['country_active'] = $this->request->getPost('radActive');
                $data_post['country_support_insurance'] = $this->request->getPost('radSupportInsurance');


                if (empty($data_post['country_code'])) {
                    $messages['code'] = 'Code field is required.';
                }

                if (empty($data_post['country_order'])) {
                    $messages['country_order'] = "Order field is required.";
                } else if (!is_numeric($data_post['country_order'])) {
                    $messages['country_order'] = "Order is not valid";
                }
            }
            if (empty($messages)) {
                switch ($save_mode) {
                    case 'general':

                        $country_model->setCountryCode($data_post['country_code']);
                        $country_model->setCountryOrder($data_post['country_order']);
                        $country_model->setCountryAdsLabel($data_post['country_ads_label']);
                        $country_model->setCountryPhoneCode($data_post['country_phone_code']);
                        $country_model->setCountryAreaId($data_post['country_area_id']);
                        $country_model->setCountryActive($data_post['country_active']);
                        $country_model->setCountrySupportInsurance($data_post['country_support_insurance']);
                        $result = $country_model->update();
                        $info = "General";

                        break;
                    case $this->globalVariable->defaultLanguage :
                        $country_model->setCountryNationality($data_post['country_nationality']);
                        $country_model->setCountryName($data_post['country_name']);
                        $result = $country_model->update();

                        $info = $arr_language[$save_mode];
                        break;
                    default:
                        $country_lang_model = CountryLang::findFirstByIdAndLang($id_country, $save_mode);
                        if (!$country_lang_model) {
                            $country_lang_model = new ScCountryLang();
                            $country_lang_model->setCountryId($id_country);
                            $country_lang_model->setCountryLangCode($save_mode);
                        }
                        $country_lang_model->setCountryName($data_post['country_name']);
                        $country_lang_model->setCountryNationality($data_post['country_nationality']);
                        $result = $country_lang_model->save();
                        $info = $arr_language[$save_mode];
                        break;
                }
                if ($result) {
                    $messages = array(
                        'message' => ucfirst($info . " Update Country success"),
                        'typeMessage' => "success",
                    );
                } else {
                    $messages = array(
                        'message' => "Update Country fail",
                        'typeMessage' => "error",
                    );
                }
            }
        }
        $item = array(
            'country_name' => ($save_mode === $this->globalVariable->defaultLanguage) ? $data_post['country_name'] : $country_model->getCountryName(),
            'country_nationality' => ($save_mode === $this->globalVariable->defaultLanguage) ? $data_post['country_nationality'] : $country_model->getCountryNationality(),
        );
        $arr_translate[$lang_default] = $item;
        $arr_country_lang = ScCountryLang::findById($id_country);
        foreach ($arr_country_lang as $country_lang) {
            $item = array(
                'country_name' => ($save_mode === $country_lang->getCountryLangCode()) ? $data_post['country_name'] : $country_lang->getCountryName(),
                'country_nationality' => ($save_mode === $country_lang->getCountryLangCode()) ? $data_post['country_nationality'] : $country_lang->getCountryNationality(),
            );
            $arr_translate[$country_lang->getCountryLangCode()] = $item;
        }
        if (!isset($arr_translate[$save_mode]) && isset($arr_language[$save_mode])) {
            $item = array(
                'country_name' => $data_post['country_name'],
                'country_nationality' => $data_post['country_nationality'],
            );
            $arr_translate[$save_mode] = $item;
        }
        $formData = array(
            'country_id' => $country_model->getCountryId(),
            'country_code' => ($save_mode === 'general') ? $data_post['country_code'] : $country_model->getCountryCode(),
            'country_order' => ($save_mode === 'general') ? $data_post['country_order'] : $country_model->getCountryOrder(),
            'country_ads_label' => ($save_mode === 'general') ? $data_post['country_ads_label'] : $country_model->getCountryAdsLabel(),
            'country_phone_code' => ($save_mode === 'general') ? $data_post['country_phone_code'] : $country_model->getCountryPhoneCode(),
            'country_active' => ($save_mode === 'general') ? $data_post['country_active'] : $country_model->getCountryActive(),
            'country_support_insurance' => ($save_mode === 'general') ? $data_post['country_support_insurance'] : $country_model->getCountrySupportInsurance(),
            'country_area_id' => ($save_mode === 'general') ? $data_post['country_area_id'] : $country_model->getCountryAreaId(),
            'arr_translate' => $arr_translate,
            'arr_language' => $arr_language,
            'lang_default' => $lang_default,
            'lang_current' => $lang_current
        );
        $messages['status'] = 'border-red';
        $select_area = Area::getAreaCombobox($formData['country_area_id']);
        $this->view->setVars(array(
            'formData' => $formData,
            'messages' => $messages,
            'select_area' => $select_area
        ));
    }

    public function deleteAction()
    {
        $country_checked = $this->request->getPost("item");
        $lang = $this->request->get("slcLang");
        $msg_result =array();
        if (!empty($country_checked)) {
            $total = 0;
            foreach ($country_checked as $country_id) {
                if ($lang != $this->globalVariable->defaultLanguage) {
                    $country_item = CountryLang::findFirstByIdAndLang($country_id, $lang);
                } else {
                    $country_item = Country::findFirstById($country_id);
                }
                if ($country_item) {
                    if ($country_item->delete() === false) {
                        $message_delete = 'Can\'t delete the Country Name = ' . $country_item->getCountryName();
                        $msg_result['status'] = 'error';
                        $msg_result['msg'] = $message_delete;
                    } else {
                        $total ++;
                        if ($lang == $this->globalVariable->defaultLanguage){
                            CountryLang::deleteById($country_id);
                        }
                    }
                }
            }
            if ($total > 0) {
                if ($total == 1){
                    $message_delete = 'Delete ' . $total . ' Country successfully.';
                } else{
                    $message_delete = 'Delete ' . $total . ' Countries successfully.';
                }

                $msg_result['status'] = 'success';
                $msg_result['msg'] = $message_delete;
            }
            $this->session->set('msg_result', $msg_result);
            return $this->response->redirect("/dashboard/list-country");
        }
    }


    private function getParameter($keyword, $langCode)
    {
        $validator = new Validator();
        $arrParameter = array();
        if ($langCode === $this->globalVariable->defaultLanguage) {
            $sql = "SELECT p.* FROM Score\Models\ScCountry p WHERE 1";
            if (!empty($keyword)) {
                $sql .= " AND country_id = :keyword: OR country_name like CONCAT('%',:keyword:,'%')";
                $arrParameter['keyword'] = $keyword;
                $this->dispatcher->setParam("txtSearch", $keyword);
            }
        } else {
            $sql = "SELECT p.*, pl.* FROM Score\Models\ScCountry p 
                    INNER JOIN \Score\Models\ScCountryLang pl ON pl.country_id  = p.country_id  AND  pl.country_lang_code  = :lang_code:                           
                    WHERE 1";
            $arrParameter['lang_code'] = $langCode;
            $this->dispatcher->setParam("slcLang", $langCode);
            if (!empty($keyword)) {
                if ($validator->validInt($keyword)) {
                    $sql .= " AND p.country_id = :number:";
                    $arrParameter['number'] = $keyword;
                } else {
                    $sql .= " AND pl.country_name like CONCAT('%',:keyword:,'%')";

                    $arrParameter['keyword'] = $keyword;
                }
                $this->dispatcher->setParam("txtSearch", $keyword);
            }
        }
        $sql .= " ORDER BY p.country_name ASC";
        $data['para'] = $arrParameter;
        $data['sql'] = $sql;
        return $data;
    }
    public function getcountrybyareaAction() {
        $area_id = $this->request->getPost('area_id');
        $country_code = $this->request->getPost('country_code');
        $select_country = Country::getComboboxByCodeAndAreaId($country_code,$area_id);
        die(json_encode($select_country));
    }
}
