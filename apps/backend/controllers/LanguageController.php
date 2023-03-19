<?php
namespace Score\Backend\Controllers;

use Score\Repositories\Country;
use Score\Repositories\Language;
use Score\Models\ScLanguage;
use Score\Utils\Validator;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;

class LanguageController extends ControllerBase
{
    public function indexAction()
    {
        $current_page = $this->request->get('page');
        $validator = new Validator();
        if ($validator->validInt($current_page) == false || $current_page < 1)
            $current_page = 1;
        $keyword = trim($this->request->get("txtSearch"));
        $sql = ScLanguage::query();
        if (!empty($keyword)) {
            if ($validator->validInt($keyword)) {
                $sql->where("language_id = :keyword:",["keyword" => $keyword]);
            } else {
                $sql->where("language_name like CONCAT('%',:keyword:,'%') OR language_code like CONCAT('%',:keyword:,'%')",["keyword" => $keyword]);
            }
            $this->dispatcher->setParam("txtSearch", $keyword);
        }
        $sql->orderBy("language_name");
        $list_language = $sql->execute();
        $paginator = new PaginatorModel(array(
            'data' => $list_language,
            'limit' => 20,
            'page' => $current_page,
        ));
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
        $this->view->list_language = $paginator->getPaginate();
    }


    public function createAction()
    {
        $data = array('language_id' => -1,  'language_active' => 'Y', 'language_order' => 1);
        if ($this->request->isPost()) {
            $messages = array();
            $data = array(
                'language_name' => $this->request->getPost("txtName", array('string', 'trim')),
                'language_code' => $this->request->getPost("txtCode", array('string', 'trim')),
                'language_code_time' => $this->request->getPost("txtCodeTime", array('string', 'trim')),
                'language_country_code' => $this->request->getPost("slcCountry", array('string', 'trim')),
                'language_order' => $this->request->getPost("txtOrder", array('string', 'trim')),
                'language_active' => $this->request->getPost("radActive"),
            );
            if (empty($data["language_name"])) {
                $messages["name"] = "Name field is required.";
            }
            if ($data['language_code'] == "") {
                $messages['code'] = 'Code field is required.';
            } else if (Language::checkCode($data['language_code'], -1)) {
                $messages["code"] = "Code is exists.";
            }
            if (empty($data['language_order'])) {
                $messages["order"] = "Order field is required.";
            } else if (!is_numeric($data["language_order"])) {
                $messages["order"] = "Order is not valid ";
            }
            if (count($messages) == 0) {
                $msg_result = array();
                $new_language = new ScLanguage();
                if ($new_language->save($data)) {
                    $msg_result = array('status' => 'success', 'msg' => 'Create Language Success');
                } else {
                    $message = "We can't store your info now: \n";
                    foreach ($new_language->getMessages() as $msg) {
                        $message .= $msg . "\n";
                    }
                    $msg_result['status'] = 'error';
                    $msg_result['msg'] = $message;
                }

                $this->session->set('msg_result', $msg_result);
                return $this->response->redirect("/dashboard/list-language");
            }
        }
        $select_country = Country::getComboboxByCode($data['language_country_code']);
        $messages["status"] = "border-red";
        $this->view->setVars([
            'formData' => $data,
            'messages' => $messages,
            'select_country' => $select_country,
        ]);
    }

    public function editAction()
    {
        $id = $this->request->get('id');
        $checkID = new Validator();
        if (!$checkID->validInt($id)) {
            return $this->response->redirect('notfound');
        }
        $language_model = Language::findFirstById($id);
        if (empty($language_model)) {
            return $this->response->redirect('notfound');
        }
        $model_data = array(
            'language_id' => $language_model->getLanguageId(),
            'language_name' => $language_model->getLanguageName(),
            'language_code' => $language_model->getLanguageCode(),
            'language_code_time' => $language_model->getLanguageCodeTime(),
            'language_country_code' => $language_model->getLanguageCountryCode(),
            'language_order' => $language_model->getLanguageOrder(),
            'language_active' => $language_model->getLanguageActive(),
        );
        $input_data = $model_data;
        $messages = array();
        if ($this->request->isPost()) {
            $data = array(
                'language_id' => $id,
                'language_name' => $this->request->getPost("txtName", array('string', 'trim')),
                'language_code' => $this->request->getPost("txtCode", array('string', 'trim')),
                'language_code_time' => $this->request->getPost("txtCodeTime", array('string', 'trim')),
                'language_country_code' => $this->request->getPost("slcCountry", array('string', 'trim')),
                'language_order' => $this->request->getPost("txtOrder", array('string', 'trim')),
                'language_active' => $this->request->getPost("radActive"),
            );
            $input_data = $data;
            if (empty($data["language_name"])) {
                $messages["name"] = "Name field is required.";
            }
            if ($data['language_code'] == "") {
                $messages['code'] = 'Code field is required.';
            } else if (Language::checkCode($data['language_code'], $data['language_id'])) {
                $messages["code"] = "Code is exists.";
            }
            if (empty($data['language_order'])) {
                $messages["order"] = "Order field is required.";
            } else if (!is_numeric($data["language_order"])) {
                $messages["order"] = "Order is not valid ";
            }
            if (count($messages) == 0) {
                $msg_result = array();
                if ($language_model->update($data)) {
                    $msg_result = array('status' => 'success', 'msg' => 'Edit language Success');
                } else {
                    $message = "We can't store your info now: \n";
                    foreach ($language_model->getMessages() as $msg) {
                        $message .= $msg . "\n";
                    }
                    $msg_result['status'] = 'error';
                    $msg_result['msg'] = $message;
                }
                $this->session->set('msg_result', $msg_result);
                return $this->response->redirect("/dashboard/list-language");
            }
        }
        $select_country = Country::getComboboxByCode($input_data['language_country_code']);
        $messages["status"] = "border-red";
        $this->view->setVars([
            'formData' => $input_data,
            'messages' => $messages,
            'select_country' => $select_country,
        ]);
    }

    public function deleteAction()
    {
        $language_checked = $this->request->getPost("item");
        if (!empty($language_checked)) {
            $tn_log = array();
            foreach ($language_checked as $id) {
                $language_item = Language::findFirstById($id);
                if ($language_item) {
                    $msg_result = array();
                    if ($language_item->delete() === false) {
                        $message_delete = 'Can\'t delete the Language Name = ' . $language_item->getLanguageName();
                        $msg_result['status'] = 'error';
                        $msg_result['msg'] = $message_delete;
                    } else {
                        $tn_log[$id] = $language_item->toArray();
                    }
                }
            }
            if (count($tn_log) > 0) {
                $message_delete = 'Delete ' . count($tn_log) . ' Language successfully.';
                $msg_result['status'] = 'success';
                $msg_result['msg'] = $message_delete;
            }
            $this->session->set('msg_result', $msg_result);
            return $this->response->redirect("/dashboard/list-language");
        }
    }
}
