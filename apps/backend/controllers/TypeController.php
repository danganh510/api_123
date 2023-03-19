<?php

namespace Score\Backend\Controllers;

use Score\Repositories\Language;
use Score\Models\ScLanguage;
use Score\Models\ScType;
use Score\Models\ScTypeLang;
use Score\Repositories\Article;
use Score\Repositories\Country;
use Score\Repositories\Type;
use Score\Repositories\TypeLang;
use Score\Utils\Validator;
use Phalcon\Paginator\Adapter\NativeArray;

class TypeController extends ControllerBase
{
    protected $global_location_country_code;

    public function initialize()
    {
        parent::initialize();
    }

    public function indexAction()
    {
        $selectAll = '';
        $keyword = trim($this->request->get("txtSearch"));
  

        $data = $this->getParameter();
        $list_page = $this->modelsManager->executeQuery($data['sql'], $data['para']);
        $current_page = $this->request->get('page');
        $validator = new Validator();
        if ($validator->validInt($current_page) == false || $current_page < 1)
            $current_page = 1;
        $msg_result = array();
        if ($this->session->has('msg_result')) {
            $msg_result = $this->session->get('msg_result');
            $this->session->remove('msg_result');
        }
        $msg_delete = array();
        if ($this->session->has('msg_delete')) {
            $msg_delete = $this->session->get('msg_delete');
            $this->session->remove('msg_delete');
        }
        $lang_search = isset($data["para"]["lang_code"]) ? $data["para"]["lang_code"] : $this->globalVariable->defaultLanguage;
        $result = array();
        if ($list_page && sizeof($list_page) > 0) {
            if ($lang_search != $this->globalVariable->defaultLanguage) {
                foreach ($list_page as $item) {
                    $result[] = \Phalcon\Mvc\Model::cloneResult(
                        new ScType(), array_merge($item->t->toArray(), $item->tl->toArray()));
                }
            } else {
                foreach ($list_page as $item) {
                    $result[] = \Phalcon\Mvc\Model::cloneResult(new ScType(), $item->toArray());
                }
            }
        }
        $paginator = new NativeArray(
            [
                'data' => $result,
                'limit' => 20,
                'page' => $current_page,
            ]
        );
        $type = new Type();
        $type_search = isset($data["para"]["type_id"]) ? $data["para"]["type_id"] : 0;
        $select_type = $type->getParentIdType("", 0, $type_search);
        $this->view->setVars(array(
            'page' => $paginator->getPaginate(),
            'msg_result' => $msg_result,
            'msg_delete' => $msg_delete,
            'select_type' => $select_type,
        ));
    }

    public function createAction()
    {
        $data = array('id' => -1, 'order' => 1, 'active' => 'Y','type_parent_id' => 0);
        $messages = array();
        if ($this->request->isPost()) {
            $messages = array();
            $data = array(
                'id' => -1,
                'type_parent_id' => $this->request->getPost("slcParent"),
                'name' => trim($this->request->getPost("txtName")),
                'title' => $this->request->getPost("txtTitle", array('string', 'trim')),
                'keyword' => $this->request->getPost("txtKeyword", array('string', 'trim')),
                'meta_description' => $this->request->getPost("txtMetades", array('string', 'trim')),
                'meta_image' => $this->request->getPost("txtMetaImage", array('string', 'trim')),
                'meta_keyword' => $this->request->getPost("txtMetakey", array('string', 'trim')),
                'order' => $this->request->getPost("txtOrder", array('string', 'trim')),
                'active' => $this->request->getPost("radActive"),
            );

            if (empty($data["name"])) {
                $messages["name"] = "Name field is required.";
            }
            if (empty($data["title"])) {
                $messages["title"] = "Title field is required.";
            }
            if (empty($data["keyword"])) {
                $messages["keyword"] = "Keyword field is required.";
            }elseif (Type::checkKeyword($data["keyword"], -1))
                $messages["keyword"] = "Keyword field is exist.";

            if (empty($data["meta_description"])) {
                $messages["meta_description"] = "Meta description field is required.";
            }
            if (empty($data["meta_keyword"])) {
                $messages["meta_keyword"] = "Meta keyword field is required.";
            }
            if (empty($data['order'])) {
                $messages['order'] = "Order field is required.";
            } else if (!is_numeric($data['order'])) {
                $messages['order'] = "Order is not valid";
            }

            if (count($messages) == 0) {
                $msg_result = array();
                $new_page = new ScType();
                $new_page->setTypeParentId($data["type_parent_id"]);
                $new_page->setTypeName($data["name"]);
                $new_page->setTypeTitle($data["title"]);
                $new_page->setTypeKeyword($data["keyword"]);
                $new_page->setTypeMetaDescription($data["meta_description"]);
                $new_page->setTypeMetaImage($data["meta_image"]);
                $new_page->setTypeMetaKeyword($data["meta_keyword"]);
                $new_page->setTypeOrder($data["order"]);
                $new_page->setTypeActive($data["active"]);
                $result = $new_page->save();

                if ($result === true) {
                    $message = 'Create the type ID: ' . $new_page->getTypeId() . ' success';
                    $msg_result = array('status' => 'success', 'msg' => $message);
                } else {
                    $message = "We can't store your info now: <br/>";
                    foreach ($new_page->getMessages() as $msg) {
                        $message .= $msg . "<br/>";
                    }
                    $msg_result['status'] = 'error';
                    $msg_result['msg'] = $message;
                }
                $this->session->set('msg_result', $msg_result);
                return $this->response->redirect("dashboard/list-type");
            }
        }

        $select_type = Type::getParentIdType("", 0, $data["type_parent_id"]);
        $messages["status"] = "border-red";
        $this->view->setVars([
            'oldinput' => $data,
            'messages' => $messages,
            'select_type' => $select_type,
        ]);
    }

    public function editAction()
    {
        $type_id = $this->request->get('id');
        $lang_current = $this->request->get('slcLang');
       $lang_current = $lang_current ? $lang_current : $this->globalVariable->defaultLanguage;
        $checkID = new Validator();
        if (!$checkID->validInt($type_id)) {
            $this->response->redirect('notfound');
            return;
        }
        $type_model = Type::findFirstById($type_id);
        if (empty($type_model)) {
            $this->response->redirect('notfound');
            return;
        }
        $arr_translate = array();
        $messages = array();
        $data_post = array(
            'type_id ' => -1,
            'type_active' => '',
            'type_name' => '',
            'type_title' => '',
            'type_keyword ' => '',
            'type_meta_keyword' => '',
            'type_meta_description' => '',
            'type_meta_image' => '',
            'type_parent_id' => '',
            'type_order' => '',
        );
        $save_mode = '';
        $arr_language = Language::arrLanguages();
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
            if ($save_mode != ScLanguage::GENERAL) {
                $data_post['type_name'] = $this->request->getPost('txtName', array('string', 'trim'));
                $data_post['type_title'] = $this->request->getPost('txtTitle', array('string', 'trim'));
                $data_post['type_meta_keyword'] = $this->request->getPost('txtMetaKey', array('string', 'trim'));
                $data_post['type_meta_description'] = $this->request->getPost('txtMetaDesc', array('string', 'trim'));
                if (empty($data_post['type_name'])) {
                    $messages[$save_mode]['name'] = 'Name field is required.';
                }
                if (empty($data_post['type_title'])) {
                    $messages[$save_mode]['title'] = 'Title field is required.';
                }
                if (empty($data_post['type_meta_keyword'])) {
                    $messages[$save_mode]['meta_keyword'] = 'Meta keyword field is required.';
                }
                if (empty($data_post['type_meta_description'])) {
                    $messages[$save_mode]['meta_description'] = 'Meta description field is required.';
                }
            } else {
                $data_post['type_parent_id'] = $this->request->getPost('slcParent');
                $data_post['type_keyword'] = $this->request->getPost('txtKeyword', array('string', 'trim'));
                $data_post['type_order'] = $this->request->getPost('txtOrder', array('string', 'trim'));
                $data_post['type_meta_image'] = $this->request->getPost('txtMetaImage', array('string', 'trim'));
                $data_post['type_active'] = $this->request->getPost("radActive");
                if (empty($data_post['type_keyword'])) {
                    $messages['keyword'] = 'Keyword field is required.';
                }elseif (Type::checkKeyword($data_post["type_keyword"], $type_id))
                    $messages["keyword"] = "Keyword field is exist.";
                if (empty($data_post['type_order'])) {
                    $messages['order'] = "Order field is required.";
                } else if (!is_numeric($data_post['type_order'])) {
                    $messages['order'] = "Order is not valid.";
                }
            }
            if (empty($messages)) {
                switch ($save_mode) {
                    case ScLanguage::GENERAL:#
                        $type_model->setTypeKeyword($data_post['type_keyword']);
                        $type_model->setTypeMetaImage($data_post['type_meta_image']);
                        $type_model->setTypeOrder($data_post['type_order']);
                        $type_model->setTypeParentId($data_post['type_parent_id']);
                        $type_model->setTypeActive($data_post['type_active']);
                        $result = $type_model->update();
                        $info = ScLanguage::GENERAL;
                        break;
                    case $this->globalVariable->defaultLanguage :
                        $type_model->setTypeName($data_post['type_name']);
                        $type_model->setTypeTitle($data_post['type_title']);
                        $type_model->setTypeMetaKeyword($data_post['type_meta_keyword']);
                        $type_model->setTypeMetaDescription($data_post['type_meta_description']);
                        $result = $type_model->update();
                        $info = $arr_language[$save_mode];
                        break;
                    default:
                        $type_lang_model = TypeLang::findFirstByIdAndLang($type_id, $save_mode);
                        if (!$type_lang_model) {
                            $type_lang_model = new ScTypeLang();
                            $type_lang_model->setTypeId($type_id);
                            $type_lang_model->setTypeLangCode($save_mode);
                        }
                        $type_lang_model->setTypeName($data_post['type_name']);
                        $type_lang_model->setTypeTitle($data_post['type_title']);
                        $type_lang_model->setTypeMetaKeyword($data_post['type_meta_keyword']);
                        $type_lang_model->setTypeMetaDescription($data_post['type_meta_description']);
                        $result = $type_lang_model->save();
                        $info = $arr_language[$save_mode];
                        break;
                }
                if ($result) {
                    $messages = array(
                        'message' => ucfirst($info . " Update type success"),
                        'typeMessage' => "success",
                    );
                } else {
                    $messages = array(
                        'message' => "Update type fail",
                        'typeMessage' => "error",
                    );
                }
            }
        }
        $type_model = Type::findFirstById($type_model->getTypeId());
        $item = array(
            'type_id' => $type_model->getTypeId(),
            'type_name' => ($save_mode === $this->globalVariable->defaultLanguage) ? $data_post['type_name'] : $type_model->getTypeName(),
            'type_title' => ($save_mode === $this->globalVariable->defaultLanguage) ? $data_post['type_title'] : $type_model->getTypeTitle(),
            'type_meta_keyword' => ($save_mode === $this->globalVariable->defaultLanguage) ? $data_post['type_meta_keyword'] : $type_model->getTypeMetaKeyword(),
            'type_meta_description' => ($save_mode === $this->globalVariable->defaultLanguage) ? $data_post['type_meta_description'] : $type_model->getTypeMetaDescription(),
        );
        $arr_translate[$this->globalVariable->defaultLanguage] = $item;
        $arr_type_lang = TypeLang::findById($type_id);
        foreach ($arr_type_lang as $type_lang) {
            $item = array(
                'type_id' => $type_lang->getTypeId(),
                'type_name' => ($save_mode === $type_lang->getTypeLangCode()) ? $data_post['type_name'] : $type_lang->getTypeName(),
                'type_title' => ($save_mode === $type_lang->getTypeLangCode()) ? $data_post['type_title'] : $type_lang->getTypeTitle(),
                'type_meta_keyword' => ($save_mode === $type_lang->getTypeLangCode()) ? $data_post['type_meta_keyword'] : $type_lang->getTypeMetaKeyword(),
                'type_meta_description' => ($save_mode === $type_lang->getTypeLangCode()) ? $data_post['type_meta_description'] : $type_lang->getTypeMetaDescription(),
            );
            $arr_translate[$type_lang->getTypeLangCode()] = $item;
        }
        if (!isset($arr_translate[$save_mode]) && isset($arr_language[$save_mode])) {
            $item = array(
                'type_id' => -1,
                'type_name' => $data_post['type_name'],
                'type_title' => $data_post['type_title'],
                'type_meta_keyword' => $data_post['type_meta_keyword'],
                'type_meta_description' => $data_post['type_meta_description'],
            );
            $arr_translate[$save_mode] = $item;
        }
        $formData = array(
            'type_id' => $type_model->getTypeId(),
            'type_parent_id' => ($save_mode === ScLanguage::GENERAL) ? $data_post['type_parent_id'] : $type_model->getTypeParentId(),
            'type_order' => ($save_mode === ScLanguage::GENERAL) ? $data_post['type_order'] : $type_model->getTypeOrder(),
            'type_keyword' => ($save_mode === ScLanguage::GENERAL) ? $data_post['type_keyword'] : $type_model->getTypeKeyword(),
            'type_meta_image' => ($save_mode === ScLanguage::GENERAL) ? $data_post['type_meta_image'] : $type_model->getTypeMetaImage(),
            'type_active' => ($save_mode === ScLanguage::GENERAL) ? $data_post['type_active'] : $type_model->getTypeActive(),
            'arr_translate' => $arr_translate,
            'arr_language' => $arr_language,
            'lang_current' => $lang_current
        );
        $messages["status"] = "border-red";
        $select_type = Type::getParentIdType("", 0, $formData["type_parent_id"]);
        $this->view->setVars([
            'formData' => $formData,
            'messages' => $messages,
            'select_type' => $select_type,
        ]);
    }

    public function deleteAction()
    {
        $items_checked = $this->request->getPost("item");
        if (!empty($items_checked)) {
            $msg_result = array();
            $total = 0;
            foreach ($items_checked as $id) {
                $item = Type::findFirstById($id);
                $table = array();
                if ($item) {
                    if (count($table) > 0) {
                        $message_delete = 'Can\'t delete the Type Name = ' . $item->getTypeName().' Because has item in ' . implode(', ',$table)."<br>";
                        $msg_delete['status'] = 'error';
                        $msg_delete['msg'] .= $message_delete;
                    } else {
                        if ($item->delete() === false) {
                            $message_delete = 'Can\'t delete the  Type Name = ' . $item->getTypeName()."<br>";
                            $msg_delete['status'] = 'error';
                            $msg_delete['msg'] .= $message_delete;
                        } else {
                            $total++;
                           
                            if ($lang == $this->globalVariable->defaultLanguage) {
                                TypeLang::deleteById($id);
                            }
                        }
                    }
                }
            }
            if ($total > 0) {
                $message_delete = 'Delete ' . $total . ' Type successfully.'."<br>";
                $msg_result['status'] = 'success';
                $msg_result['msg'] = $message_delete;

            }
            $this->session->set('msg_result', $msg_result);
            $this->session->set('msg_delete', $msg_delete);
            return $this->response->redirect('/dashboard/list-type');
        }
    }

    private function getParameter()
    {
        $lang = $this->request->get("slcLang", array('string', 'trim'));
        $type = $this->request->get("slType");
        $keyword = trim($this->request->get("txtSearch"));
        $langCode = !empty($lang) ? $lang : $this->globalVariable->defaultLanguage;
        $this->dispatcher->setParam("slcLang", $langCode);
        $arrParameter = [];

        $validator = new Validator();
        if ($langCode === $this->globalVariable->defaultLanguage) {
            $sql = "SELECT t.* FROM Score\Models\ScType t WHERE 1";
            if (!empty($keyword)) {
                if ($validator->validInt($keyword)) {
                    $sql .= " AND (t.type_id = :number:)";
                    $arrParameter['number'] = $keyword;
                } else {
                    if ($match == 'match') {
                        $sql .= " AND (t.type_name =:keyword: OR t.type_title =:keyword:
                                     OR t.type_meta_keyword =:keyword: OR t.type_meta_description =:keyword:  
                                    )";
                    } else {
                        $sql .= " AND (t.type_name like CONCAT('%',:keyword:,'%') OR t.type_title like CONCAT('%',:keyword:,'%')
                                     OR t.type_meta_keyword like CONCAT('%',:keyword:,'%') OR t.type_meta_description like CONCAT('%',:keyword:,'%')
                                     )";
                    }
                    $arrParameter['keyword'] = $keyword;
                }
                $this->dispatcher->setParam("txtSearch", $keyword);
            }
        } else {
            $sql = "SELECT t.*, tl.* FROM Score\Models\ScType t 
                    INNER JOIN \Score\Models\ScTypeLang tl
                                ON tl.type_id = t.type_id  AND  tl.type_lang_code = :lang_code:                           
                    WHERE 1";
            $arrParameter['lang_code'] = $langCode;
            $this->dispatcher->setParam("slcLang", $langCode);
            if (!empty($keyword)) {
                if ($validator->validInt($keyword)) {
                    $sql .= " AND (t.type_id = :number:)";
                    $arrParameter['number'] = $keyword;
                } else {
                    if ($match == 'match') {
                        $sql .= " AND (tl.type_name =:keyword: OR tl.type_title =:keyword:
                                     OR tl.type_meta_keyword =:keyword: OR tl.type_meta_description =:keyword:
                                    )";
                    } else {
                        $sql .= " AND (tl.type_name like CONCAT('%',:keyword:,'%') OR tl.type_title like CONCAT('%',:keyword:,'%')
                                     OR tl.type_meta_keyword like CONCAT('%',:keyword:,'%') OR tl.type_meta_description like CONCAT('%',:keyword:,'%')
                                     )";
                    }
                    $arrParameter['keyword'] = $keyword;
                }
                $this->dispatcher->setParam("txtSearch", $keyword);
            }
        }
        if (!empty($type)) {
            if ($validator->validInt($type) == false) {
                $this->response->redirect("/notfound");
                return;
            }
            $sql .= " AND t.type_parent_id = :type_id:";
            $arrParameter["type_id"] = $type;
            $this->dispatcher->setParam("slType", $type);
        }
        $sql .= "  ORDER BY t.type_id DESC";
        $data['para'] = $arrParameter;
        $data['sql'] = $sql;
        return $data;
    }

}