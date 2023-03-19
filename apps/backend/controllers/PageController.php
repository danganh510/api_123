<?php

namespace Score\Backend\Controllers;

use Score\Models\ScLanguage;
use Score\Models\ScPage;
use Score\Models\ScPageLang;
use Score\Repositories\Country;
use Score\Repositories\Location;
use Score\Repositories\Page;
use Score\Repositories\PageLang;
use Score\Utils\Validator;
use Phalcon\Paginator\Adapter\NativeArray;
use Score\Repositories\Language;

class PageController extends ControllerBase
{
    protected $global_location_country_code;

    public function initialize()
    {
        $this->global_location_country_code = strtoupper($this->globalVariable->global['code']);
        parent::initialize();
    }

    public function indexAction()
    {
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
                        new ScPage(), array_merge($item->p->toArray(), $item->pl->toArray()));
                }
            } else {
                foreach ($list_page as $item) {
                    $result[] = \Phalcon\Mvc\Model::cloneResult(new ScPage(), $item->toArray());
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
        $this->view->setVars(array(
            'page' => $paginator->getPaginate(),
            'msg_result' => $msg_result,
            'msg_delete' => $msg_delete,
        ));
    }

    public function createAction()
    {
        $data = array('id' => -1,'active' => 'Y','is_landing_page' => 'N');
        $messages = array();
        if ($this->request->isPost()) {
            $messages = array();
            $data = array(
                'id' => -1,
                'name' => $this->request->getPost("txtName", array('string', 'trim')),
                'title' => $this->request->getPost("txtTitle", array('string', 'trim')),
                'keyword' => $this->request->getPost("txtKeyword", array('string', 'trim')),
                'meta_description' => $this->request->getPost("txtMetades", array('string', 'trim')),
                'meta_image' => $this->request->getPost("txtMetaImage", array('string', 'trim')),
                'meta_keyword' => $this->request->getPost("txtMetakey", array('string', 'trim')),
                'style' => $this->request->getPost("txtStyle"),
                'content' => $this->request->getPost("txtContent"),
                'active' => $this->request->getPost("radActive"),
                'is_landing_page' => $this->request->getPost("txtLanding"),
            );
            if (empty($data["name"])) {
                $messages["name"] = "Name field is required.";
            }
            if (empty($data["title"])) {
                $messages["title"] = "Title field is required.";
            }
            if (empty($data["keyword"])) {
                $messages["keyword"] = "Keyword field is required.";
            }else if (Page::checkKeyword($data["keyword"], -1)) {
                $messages["keyword"] = "Keyword field is exist.";
            }
            if (empty($data["meta_description"])) {
                $messages["meta_description"] = "Meta description field is required.";
            }
            if (empty($data["meta_keyword"])) {
                $messages["meta_keyword"] = "Meta keyword field is required.";
            }

            if (count($messages) == 0) {
                $msg_result = array();
                $new_page = new ScPage();
                $new_page->setPageName($data["name"]);
                $new_page->setPageTitle($data["title"]);
                $new_page->setPageKeyword($data["keyword"]);
                $new_page->setPageMetaDescription($data["meta_description"]);
                $new_page->setPageMetaImage($data["meta_image"]);
                $new_page->setPageMetaKeyword($data["meta_keyword"]);
                $new_page->setPageStyle($data["style"]);
                $new_page->setPageContent($data["content"]);
                $new_page->setPageActive($data["active"]);
                $new_page->setPageIsLandingPage($data["is_landing_page"]);
                $result = $new_page->save();

                if ($result === true) {
                    $message = 'Create the Page ID: ' . $new_page->getPageId() . ' success';
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
                return $this->response->redirect("/dashboard/list-page");
            }
        }
        $messages["status"] = "border-red";
        $this->view->setVars([
            'oldinput' => $data,
            'messages' => $messages,
        ]);
    }

    public function editAction()
    {
        ini_set('memory_limit', '128M');
        $page_id = $this->request->get('id');
        $lang_current = $this->request->get('slcLang');
        $lang_current = $lang_current ? $lang_current : $this->globalVariable->defaultLanguage;
        $checkID = new Validator();
        if (!$checkID->validInt($page_id)) {
            $this->response->redirect('notfound');
            return;
        }
        $page_model = Page::findFirstById($page_id);
        if (empty($page_model)) {
            $this->response->redirect('notfound');
            return;
        }
        $arr_translate = array();
        $messages = array();
        $data_post = array(
            'page_id' => -1,
            'page_name' => '',
            'page_title' => '',
            'page_keyword' => '',
            'page_meta_keyword' => '',
            'page_meta_description' => '',
            'page_meta_image' => '',
            'page_style' => '',
            'page_content' => '',
            'page_active' => '',
            'page_is_landing_page' => '',
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
                $data_post['page_name'] = $this->request->getPost('txtName', array('string', 'trim'));
                $data_post['page_title'] = $this->request->getPost('txtTitle', array('string', 'trim'));
                $data_post['page_meta_keyword'] = $this->request->getPost('txtMetaKey', array('string', 'trim'));
                $data_post['page_meta_description'] = $this->request->getPost('txtMetaDesc', array('string', 'trim'));
                $data_post['page_content'] = $this->request->getPost('txtContent');
                $data_post['page_keyword'] = $this->request->getPost('txtKeyword', array('string', 'trim'));
                if (empty($data_post['page_name'])) {
                    $messages[$save_mode]['name'] = 'Name field is required.';
                }
                if (empty($data_post['page_keyword'])) {
                    $messages[$save_mode]['page_keyword'] = 'Keyword field is required.';
                }
                if (empty($data_post['page_title'])) {
                    $messages[$save_mode]['title'] = 'Title field is required.';
                }
                if (empty($data_post['page_meta_keyword'])) {
                    $messages[$save_mode]['meta_keyword'] = 'Meta keyword field is required.';
                }
                if (empty($data_post['page_meta_description'])) {
                    $messages[$save_mode]['meta_description'] = 'Meta description field is required.';
                }
            } else {
                $data_post['page_style'] = $this->request->getPost('txtStyle');
                $data_post['page_meta_image'] = $this->request->getPost('txtMetaImage', array('string', 'trim'));
                $data_post['page_active'] = $this->request->getPost('radActive');
                $data_post['page_is_landing_page'] = $this->request->getPost('radLanding');
            }
            if (empty($messages)) {
                switch ($save_mode) {
                    case ScLanguage::GENERAL:
                        $page_model->setPageStyle($data_post['page_style']);
                        $page_model->setPageMetaImage($data_post['page_meta_image']);
                        $page_model->setPageActive($data_post['page_active']);
                        $page_model->setPageIsLandingPage($data_post['page_is_landing_page']);
                        $result = $page_model->update();
                        $info = ScLanguage::GENERAL;
                        break;
                    case $this->globalVariable->defaultLanguage :
                        $page_model->setPageKeyword($data_post['page_keyword']);
                        $page_model->setPageName($data_post['page_name']);
                        $page_model->setPageTitle($data_post['page_title']);
                        $page_model->setPageMetaKeyword($data_post['page_meta_keyword']);
                        $page_model->setPageMetaDescription($data_post['page_meta_description']);
                        $page_model->setPageContent($data_post['page_content']);
                        $result = $page_model->update();
                        $info = $arr_language[$save_mode];
                        break;
                    default:
                        $page_lang_model = PageLang::findFirstByIdAndLang($page_id, $save_mode);
                        if (!$page_lang_model) {
                            $page_lang_model = new ScPageLang();
                            $page_lang_model->setPageId($page_id);
                            $page_lang_model->setPageLangCode($save_mode);
                        }
                        $page_lang_model->setPageKeyword($data_post['page_keyword']);
                        $page_lang_model->setPageName($data_post['page_name']);
                        $page_lang_model->setPageTitle($data_post['page_title']);
                        $page_lang_model->setPageMetaKeyword($data_post['page_meta_keyword']);
                        $page_lang_model->setPageMetaDescription($data_post['page_meta_description']);
                        $page_lang_model->setPageContent($data_post['page_content']);
                        $result = $page_lang_model->save();
                        $info = $arr_language[$save_mode];
                        break;
                }
                if ($result) {
                    $messages = array(
                        'message' => ucfirst($info . " Update Page success"),
                        'typeMessage' => "success",
                    );
                } else {
                    $messages = array(
                        'message' => "Update Page fail",
                        'typeMessage' => "error",
                    );
                }
            }
        }
        $page_model = Page::findFirstById($page_model->getPageId());
        $item = array(
            'page_id' => $page_model->getPageId(),
            'page_keyword' => ($save_mode === $this->globalVariable->defaultLanguage) ? $data_post['page_keyword'] : $page_model->getPageKeyword(),
            'page_name' => ($save_mode === $this->globalVariable->defaultLanguage) ? $data_post['page_name'] : $page_model->getPageName(),
            'page_title' => ($save_mode === $this->globalVariable->defaultLanguage) ? $data_post['page_title'] : $page_model->getPageTitle(),
            'page_meta_keyword' => ($save_mode === $this->globalVariable->defaultLanguage) ? $data_post['page_meta_keyword'] : $page_model->getPageMetaKeyword(),
            'page_meta_description' => ($save_mode === $this->globalVariable->defaultLanguage) ? $data_post['page_meta_description'] : $page_model->getPageMetaDescription(),
            'page_content' => ($save_mode === $this->globalVariable->defaultLanguage) ? $data_post['page_content'] : $page_model->getPageContent(),
        );
        $arr_translate[$this->globalVariable->defaultLanguage] = $item;
        $arr_page_lang = PageLang::findById($page_id);
        foreach ($arr_page_lang as $page_lang) {
            $item = array(
                'page_id' => $page_lang->getPageId(),
                'page_keyword' => ($save_mode === $page_lang->getPageLangCode()) ? $data_post['page_keyword'] : $page_lang->getPageKeyword(),
                'page_name' => ($save_mode === $page_lang->getPageLangCode()) ? $data_post['page_name'] : $page_lang->getPageName(),
                'page_title' => ($save_mode === $page_lang->getPageLangCode()) ? $data_post['page_title'] : $page_lang->getPageTitle(),
                'page_meta_keyword' => ($save_mode === $page_lang->getPageLangCode()) ? $data_post['page_meta_keyword'] : $page_lang->getPageMetaKeyword(),
                'page_meta_description' => ($save_mode === $page_lang->getPageLangCode()) ? $data_post['page_meta_description'] : $page_lang->getPageMetaDescription(),
                'page_content' => ($save_mode === $page_lang->getPageLangCode()) ? $data_post['page_content'] : $page_lang->getPageContent(),
            );
            $arr_translate[$page_lang->getPageLangCode()] = $item;
        }
        if (!isset($arr_translate[$save_mode]) && isset($arr_language[$save_mode])) {
            $item = array(
                'page_id' => -1,
                'page_keyword' => $data_post['page_keyword'],
                'page_name' => $data_post['page_name'],
                'page_title' => $data_post['page_title'],
                'page_meta_keyword' => $data_post['page_meta_keyword'],
                'page_meta_description' => $data_post['page_meta_description'],
                'page_content' => $data_post['page_content'],
            );
            $arr_translate[$save_mode] = $item;
        }
        $formData = array(
            'page_id' => $page_model->getPageId(),
            'page_style' => ($save_mode === ScLanguage::GENERAL) ? $data_post['page_style'] : $page_model->getPageStyle(),
            'page_meta_image' => ($save_mode === ScLanguage::GENERAL) ? $data_post['page_meta_image'] : $page_model->getPageMetaImage(),
            'page_active' => ($save_mode === ScLanguage::GENERAL) ? $data_post['page_active'] : $page_model->getPageActive(),
            'page_is_landing_page' => ($save_mode === ScLanguage::GENERAL) ? $data_post['page_is_landing_page'] : $page_model->getPageIsLandingPage(),
            'arr_translate' => $arr_translate,
            'arr_language' => $arr_language,
            'lang_current' => $lang_current
        );
        $messages["status"] = "border-red";
        $this->view->setVars([
            'formData' => $formData,
            'messages' => $messages,
        ]);
    }

    public function deleteAction()
    {
        $items_checked = $this->request->getPost("item");

        if (!empty($items_checked)) {
            $msg_result = array();
            $count_delete = 0;
            foreach ($items_checked as $id) {
                $item = Page::findFirstById($id);
                if ($item) {
                    if ($item->delete() === false) {
                        $message_delete = 'Can\'t delete the page Name = ' . $item->getPageName();
                        $msg_result['status'] = 'error';
                        $msg_result['msg'] = $message_delete;
                    } else {
                        $count_delete++;
                        PageLang::deleteById($id);
                    }
                }
            }
            if ($count_delete > 0) {
                $message_delete = 'Delete ' . $count_delete . ' page successfully.';
                $msg_result['status'] = 'success';
                $msg_result['msg'] = $message_delete;
            }
            $this->session->set('msg_result', $msg_result);
            return $this->response->redirect('/dashboard/list-page');
        }

    }

    private function getParameter()
    {
        $lang = $this->request->get("slcLang", array('string', 'trim'));

        $keyword = trim($this->request->get("txtSearch"));
        $langCode = !empty($lang) ? $lang : $this->globalVariable->defaultLanguage;
        $this->dispatcher->setParam("slcLang", $langCode);
        $arrParameter = array();

        $match = trim($this->request->get("radMatch"));
        if ($match == '') {
            $match = 'notmatch';
        }
        $this->dispatcher->setParam("radMatch", $match);

        $validator = new Validator();
        if ($langCode === $this->globalVariable->defaultLanguage) {
            $sql = "SELECT p.* FROM Score\Models\ScPage p WHERE 1";
            if (!empty($keyword)) {
                if ($validator->validInt($keyword)) {
                    $sql .= " AND (p.page_id = :number:)";
                    $arrParameter['number'] = $keyword;
                } else {
                    if ($match == 'match') {
                        $sql .= " AND (p.page_name =:keyword: OR p.page_title =:keyword:
                                     OR p.page_meta_keyword =:keyword: OR p.page_meta_description =:keyword:
                                     OR p.page_content =:keyword:
                                    )";
                    } else {
                        $sql .= " AND (p.page_name like CONCAT('%',:keyword:,'%') OR p.page_title like CONCAT('%',:keyword:,'%')
                                     OR p.page_meta_keyword like CONCAT('%',:keyword:,'%') OR p.page_meta_description like CONCAT('%',:keyword:,'%')
                                     OR p.page_content like CONCAT('%',:keyword:,'%')
                                     )";
                    }
                    $arrParameter['keyword'] = $keyword;
                }
                $this->dispatcher->setParam("txtSearch", $keyword);
            }
        } else {
            $sql = "SELECT p.*, pl.* FROM Score\Models\ScPage p 
                    INNER JOIN \Score\Models\ScPageLang pl
                                ON pl.page_id = p.page_id AND  pl.page_lang_code = :lang_code:                           
                    WHERE 1";
            $arrParameter['lang_code'] = $langCode;
            $this->dispatcher->setParam("slcLang", $langCode);
            if (!empty($keyword)) {
                if ($validator->validInt($keyword)) {
                    $sql .= " AND (p.page_id = :number:)";
                    $arrParameter['number'] = $keyword;
                } else {
                    if ($match == 'match') {
                        $sql .= " AND (pl.page_name =:keyword: OR pl.page_title =:keyword:
                                     OR pl.page_meta_keyword =:keyword: OR pl.page_meta_description =:keyword:
                                     OR pl.page_content =:keyword:
                                    )";
                    } else {
                        $sql .= " AND (pl.page_name like CONCAT('%',:keyword:,'%') OR pl.page_title like CONCAT('%',:keyword:,'%')
                                     OR pl.page_meta_keyword like CONCAT('%',:keyword:,'%') OR pl.page_meta_description like CONCAT('%',:keyword:,'%')
                                     OR pl.page_content like CONCAT('%',:keyword:,'%')
                                     )";
                    }
                    $arrParameter['keyword'] = $keyword;
                }
                $this->dispatcher->setParam("txtSearch", $keyword);
            }
        }
        $sql .= "  ORDER BY p.page_id DESC";
        $data['para'] = $arrParameter;
        $data['sql'] = $sql;
        return $data;
    }
}