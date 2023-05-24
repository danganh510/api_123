<?php

namespace Score\Backend\Controllers;

use Score\Models\ScLanguage;
use Score\Models\ScArticle;
use Score\Models\ScArticleLang;
use Score\Repositories\Country;
use Score\Repositories\Location;
use Score\Repositories\Type;
use Score\Repositories\Article;
use Score\Repositories\ArticleLang;
use Score\Utils\Validator;
use Phalcon\Paginator\Adapter\NativeArray;
use Score\Models\ScTag;
use Score\Repositories\Language;
use Tag;

class ArticleController extends ControllerBase
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

        $data = $this->getParameter($selectAll);
        $list_article = $this->modelsManager->executeQuery($data['sql'], $data['para']);
        $current_article = $this->request->get('page');
        $validator = new Validator();
        if ($validator->validInt($current_article) == false || $current_article < 1)
            $current_article = 1;
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
        if ($list_article && sizeof($list_article) > 0) {
            if ($lang_search != $this->globalVariable->defaultLanguage) {
                foreach ($list_article as $item) {
                    $result[] = \Phalcon\Mvc\Model::cloneResult(
                        new ScArticle(),
                        array_merge($item->a->toArray(), $item->al->toArray())
                    );
                }
            } else {
                foreach ($list_article as $item) {
                    $result[] = \Phalcon\Mvc\Model::cloneResult(new ScArticle(), $item->toArray());
                }
            }
        }
        $paginator = new NativeArray(
            [
                'data' => $result,
                'limit' => 20,
                'page' => $current_article,
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
    function mb_str_split($string)
    {
        return preg_split('/(?<!^)(?!$)/u', $string);
    }
    public function createAction()
    {
        $data = array('id' => -1, 'article_order' => 1, 'article_active' => 'Y', 'article_is_home' => 'N', 'article_type_id' => "",);
        $messages = array();
        $ar_tag_id = [];
        if ($this->request->isPost()) {
            $messages = array();
            $data = array(
                'id' => -1,
                'article_type_id' => $this->request->getPost("slcType"),
                'article_name' => trim($this->request->getPost("txtName")),
                'article_icon' => $this->request->getPost("txtIcon", array('string', 'trim')),
                'article_keyword' => $this->request->getPost("txtKeyword", array('string', 'trim')),
                'article_title' => $this->request->getPost("txtTitle", array('string', 'trim')),
                'article_meta_keyword' => trim($this->request->getPost("txtMetakey")),
                'article_meta_description' => trim($this->request->getPost("txtMetades")),
                'article_meta_image' => $this->request->getPost("txtMetaImage", array('string', 'trim')),
                'article_summary' => $this->request->getPost("txtSummary"),
                'article_content' => $this->request->getPost("txtContent"),
                'article_order' => $this->request->getPost("txtOrder", array('string', 'trim')),
                'article_active' => $this->request->getPost("radActive"),
                'article_is_home' => $this->request->getPost("txtHome"),
                'article_insert_time' => $this->globalVariable->curTime,
                'article_update_time' => $this->globalVariable->curTime,
            );

            if ($data["article_type_id"] == '') {
                $messages["article_type_id"] = "Type field is required.";
            }
            if (empty($data["article_name"])) {
                $messages["article_name"] = "Name field is required.";
            }
            if (empty($data["article_keyword"])) {
                $messages["article_keyword"] = "Keyword field is required.";
            } else if (Article::checkKeyword($data["article_keyword"], -1)) {
                $messages["article_keyword"] = "Keyword field is exist.";
            }
            if (empty($data["article_title"])) {
                $messages["article_title"] = "Title field is required.";
            }
            if (empty($data["article_meta_keyword"])) {
                $messages["article_meta_keyword"] = "Meta keyword field is required.";
            }
            if (empty($data["article_meta_description"])) {
                $messages["article_meta_description"] = "Meta description field is required.";
            }
            if (empty($data['article_order'])) {
                $messages['article_order'] = "Order field is required.";
            } else if (!is_numeric($data['article_order'])) {
                $messages['article_order'] = "Order is not valid";
            }
      

            if (count($messages) == 0) {
                $msg_result = array();
                $new_article = new ScArticle();
                $new_article->setArticleTypeId($data["article_type_id"]);
                $new_article->setArticleName($data["article_name"]);
                $new_article->setArticleIcon($data["article_icon"]);
                $new_article->setArticleKeyword($data["article_keyword"]);
                $new_article->setArticleTitle($data["article_title"]);
                $new_article->setArticleMetaKeyword($data["article_meta_keyword"]);
                $new_article->setArticleMetaDescription($data["article_meta_description"]);
                $new_article->setArticleMetaImage($data["article_meta_image"]);
                $new_article->setArticleSummary($data["article_summary"]);
                $new_article->setArticleContent($data["article_content"]);
                $new_article->setArticleOrder($data["article_order"]);
                $new_article->setArticleActive($data["article_active"]);
                $new_article->setArticleIsHome($data["article_is_home"]);
                $new_article->setArticleInsertTime($data["article_insert_time"]);
                $new_article->setArticleUpdateTime($data["article_update_time"]);
                $result = $new_article->save();

                if ($result === true) {
                    $arTag = $_POST['txtTag'];
                    foreach ($arTag as $tag) {
                        if (!$tag) {
                            continue;
                        }
                        $tag_model = ScTag::findTagByName($tag);
                        if (!$tag_model) {
                            $tag_model = new ScTag();
                            $tag_model->setTagName($tag);
                            $tag_model->save();
                        }
                        $ar_tag_id[] = $tag_model->getTagId();
                    }
                    $new_article->setArticleTagId(implode(",",$ar_tag_id));
                    $new_article->save();
                    $message = 'Create the Article ID: ' . $new_article->getArticleId() . ' success';
                    $msg_result = array('status' => 'success', 'msg' => $message);
                } else {
                    $message = "We can't store your info now: <br/>";
                    foreach ($new_article->getMessages() as $msg) {
                        $message .= $msg . "<br/>";
                    }
                    $msg_result['status'] = 'error';
                    $msg_result['msg'] = $message;
                }
                $this->session->set('msg_result', $msg_result);
                return $this->response->redirect("/dashboard/list-article");
            }
        }
        $type = new Type();
        $select_type = $type->getParentIdType("", 0, $data["article_type_id"]);
        $messages["status"] = "border-red";
        $select_tag = Article::selectTag($ar_tag_id);

        $this->view->setVars([
            'oldinput' => $data,
            'messages' => $messages,
            'select_type' => $select_type,
            'select_tag' => $select_tag
        ]);
    }

    public function editAction()
    {
        $article_id = $this->request->get('id');
        $lang_current = $this->request->get('slcLang');
        $lang_current = $lang_current ? $lang_current : $this->globalVariable->defaultLanguage;
        $arr_language = Language::arrLanguages();
        if (!in_array($lang_current, array_keys($arr_language))) {
            return $this->response->redirect('notfound');
        }

        $checkID = new Validator();
        if (!$checkID->validInt($article_id)) {
            $this->response->redirect('notfound');
            return;
        }
        $article_model = Article::findFirstById($article_id);
        if (empty($article_model)) {
            $this->response->redirect('notfound');
            return;
        }
        $arr_translate = array();
        $messages = array();
        $data_post = array(
            'article_id' => -1,
            'article_type_id' => '',
            'article_name' => '',
            'article_icon' => '',
            'article_keyword' => '',
            'article_title' => '',
            'article_meta_keyword' => '',
            'article_meta_description' => '',
            'article_meta_image' => '',
            'article_summary' => '',
            'article_content' => '',
            'article_content_app' => '',
            'article_order' => '',
            'article_active' => '',
            'article_is_home' => '',
            'article_update_time' => $this->globalVariable->curTime,
        );
        $save_mode = '';
        $ar_tag_id = explode(",",$article_model->getArticleTagId());
        

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
                $data_post['article_name'] = trim($this->request->getPost('txtName'));
                $data_post['article_title'] = trim($this->request->getPost('txtTitle'));
                $data_post['article_meta_keyword'] = trim($this->request->getPost('txtMetaKey'));
                $data_post['article_meta_description'] = trim($this->request->getPost('txtMetaDesc'));
                $data_post['article_meta_image'] = trim($this->request->getPost('txtMetaImage'));
                $data_post['article_summary'] = $this->request->getPost('txtSummary');
                $data_post['article_content'] = $this->request->getPost('txtContent');
                $data_post['article_keyword'] = $this->request->getPost('txtKeyword', array('string', 'trim'));
                if (empty($data_post['article_keyword'])) {
                    $messages[$save_mode]['article_keyword'] = 'Keyword field is required.';
                }
                if (empty($data_post['article_name'])) {
                    $messages[$save_mode]['article_name'] = 'Name field is required.';
                }
                if (empty($data_post['article_title'])) {
                    $messages[$save_mode]['article_title'] = 'Title field is required.';
                }
                if (empty($data_post['article_meta_keyword'])) {
                    $messages[$save_mode]['article_meta_keyword'] = 'Meta keyword field is required.';
                }
                if (empty($data_post['article_meta_description'])) {
                    $messages[$save_mode]['article_meta_description'] = 'Meta description field is required.';
                }
            } else {
                $data_post['article_type_id'] = $this->request->getPost('slcType', array('string', 'trim'));
                $data_post['article_icon'] = $this->request->getPost('txtIcon', array('string', 'trim'));
                $data_post['article_order'] = $this->request->getPost('txtOrder', array('string', 'trim'));
                $data_post['article_active'] = $this->request->getPost("radActive");
                $data_post['article_is_home'] = $this->request->getPost("txtHome");
                $data_post['article_tag'] = trim($this->request->get("txtTag"));


                if (empty($data_post["article_type_id"])) {
                    $messages["article_type_id"] = "Type field is required.";
                }
                if (empty($data_post['article_order'])) {
                    $messages['article_order'] = "Order field is required.";
                } else if (!is_numeric($data_post['article_order'])) {
                    $messages['article_order'] = "Order is not valid.";
                }
            }

            if (empty($messages)) {
                switch ($save_mode) {
                    case ScLanguage::GENERAL:
                        $article_model->setArticleTypeId($data_post['article_type_id']);
                        $article_model->setArticleIcon($data_post['article_icon']);
                        $article_model->setArticleOrder($data_post['article_order']);
                        $article_model->setArticleActive($data_post['article_active']);
                        $article_model->setArticleIsHome($data_post['article_is_home']);
                        $article_model->setArticleUpdateTime($data_post['article_update_time']);
                        $result = $article_model->update();

                        $info = ScLanguage::GENERAL;
                        break;
                    case $this->globalVariable->defaultLanguage:
                        $article_model->setArticleKeyword($data_post['article_keyword']);
                        $article_model->setArticleName($data_post['article_name']);
                        $article_model->setArticleTitle($data_post['article_title']);
                        $article_model->setArticleMetaKeyword($data_post['article_meta_keyword']);
                        $article_model->setArticleMetaDescription($data_post['article_meta_description']);
                        $article_model->setArticleMetaImage($data_post['article_meta_image']);
                        $article_model->setArticleSummary($data_post['article_summary']);
                        $article_model->setArticleContent($data_post['article_content']);
                        $article_model->setArticleUpdateTime($data_post['article_update_time']);
                        $result = $article_model->update();

                        $info = $arr_language[$save_mode];
                        break;
                    default:
                        $article_lang_model = ArticleLang::findFirstByIdAndLang($article_id, $save_mode);
                        if (!$article_lang_model) {
                            $article_lang_model = new ScArticleLang();
                            $article_lang_model->setArticleId($article_id);
                            $article_lang_model->setArticleLangCode($save_mode);
                        }
                        $article_lang_model->setArticleKeyword($data_post['article_keyword']);
                        $article_lang_model->setArticleName($data_post['article_name']);
                        $article_lang_model->setArticleTitle($data_post['article_title']);
                        $article_lang_model->setArticleMetaKeyword($data_post['article_meta_keyword']);
                        $article_lang_model->setArticleMetaDescription($data_post['article_meta_description']);
                        $article_lang_model->setArticleMetaImage($data_post['article_meta_image']);
                        $article_lang_model->setArticleSummary($data_post['article_summary']);
                        $article_lang_model->setArticleContent($data_post['article_content']);
                        $result = $article_lang_model->save();
                        $info = $arr_language[$save_mode];
                        break;
                }
                if ($result) {
                    if($save_mode == ScLanguage::GENERAL) {
                        var_dump($_POST['txtTag']);exit;
                        
                        $arTag = $_POST['txtTag'];
                        $ar_tag_id = [];
                    
                        foreach ($arTag as $tag) {
                            if (!$tag) {
                                continue;
                            }
                            $tag_model = ScTag::findTagByName($tag);
                            if (!$tag_model) {
                                $tag_model = new ScTag();
                                $tag_model->setTagName($tag);
                                $tag_model->save();
                            }
                            $ar_tag_id[] = $tag_model->getTagId();
                        }
                        $article_model->setArticleTagId(implode(",",$ar_tag_id));
                        $article_model->save();
                    }
                   
                    $messages = array(
                        'message' => ucfirst($info . " Update article success"),
                        'typeMessage' => "success",
                    );
                } else {
                    $messages = array(
                        'message' => "Update article fail",
                        'typeMessage' => "error",
                    );
                }
            }
        }

        $article_model = Article::findFirstById($article_model->getArticleId());
        $item = array(
            'article_id' => $article_model->getArticleId(),
            'article_keyword' => ($save_mode === $this->globalVariable->defaultLanguage) ? $data_post['article_keyword'] : $article_model->getArticleKeyword(),
            'article_name' => ($save_mode === $this->globalVariable->defaultLanguage) ? $data_post['article_name'] : $article_model->getArticleName(),
            'article_title' => ($save_mode === $this->globalVariable->defaultLanguage) ? $data_post['article_title'] : $article_model->getArticleTitle(),
            'article_meta_keyword' => ($save_mode === $this->globalVariable->defaultLanguage) ? $data_post['article_meta_keyword'] : $article_model->getArticleMetaKeyword(),
            'article_meta_description' => ($save_mode === $this->globalVariable->defaultLanguage) ? $data_post['article_meta_description'] : $article_model->getArticleMetaDescription(),
            'article_meta_image' => ($save_mode === $this->globalVariable->defaultLanguage) ? $data_post['article_meta_image'] : $article_model->getArticleMetaImage(),
            'article_summary' => ($save_mode === $this->globalVariable->defaultLanguage) ? $data_post['article_summary'] : $article_model->getArticleSummary(),
            'article_content' => ($save_mode === $this->globalVariable->defaultLanguage) ? $data_post['article_content'] : $article_model->getArticleContent(),
        );
        $arr_translate[$this->globalVariable->defaultLanguage] = $item;
        $arr_article_lang = ArticleLang::findById($article_id);
        foreach ($arr_article_lang as $article_lang) {
            $item = array(
                'article_id' => $article_lang->getArticleId(),
                'article_keyword' => ($save_mode === $article_lang->getArticleLangCode()) ? $data_post['article_keyword'] : $article_lang->getArticleKeyword(),
                'article_name' => ($save_mode === $article_lang->getArticleLangCode()) ? $data_post['article_name'] : $article_lang->getArticleName(),
                'article_title' => ($save_mode === $article_lang->getArticleLangCode()) ? $data_post['article_title'] : $article_lang->getArticleTitle(),
                'article_meta_keyword' => ($save_mode === $article_lang->getArticleLangCode()) ? $data_post['article_meta_keyword'] : $article_lang->getArticleMetaKeyword(),
                'article_meta_description' => ($save_mode === $article_lang->getArticleLangCode()) ? $data_post['article_meta_description'] : $article_lang->getArticleMetaDescription(),
                'article_meta_image' => ($save_mode === $article_lang->getArticleLangCode()) ? $data_post['article_meta_image'] : $article_lang->getArticleMetaImage(),
                'article_summary' => ($save_mode === $article_lang->getArticleLangCode()) ? $data_post['article_summary'] : $article_lang->getArticleSummary(),
                'article_content' => ($save_mode === $article_lang->getArticleLangCode()) ? $data_post['article_content'] : $article_lang->getArticleContent(),
            );
            $arr_translate[$article_lang->getArticleLangCode()] = $item;
        }
        if (!isset($arr_translate[$save_mode]) && isset($arr_language[$save_mode])) {
            $item = array(
                'article_id' => -1,
                'article_keyword' => $data_post['article_keyword'],
                'article_name' => $data_post['article_name'],
                'article_title' => $data_post['article_title'],
                'article_meta_keyword' => $data_post['article_meta_keyword'],
                'article_meta_description' => $data_post['article_meta_description'],
                'article_meta_image' => $data_post['article_meta_image'],
                'article_summary' => $data_post['article_summary'],
                'article_content' => $data_post['article_content'],
               );
            $arr_translate[$save_mode] = $item;
        }
        $arTagId = $article_model->getArticleTagId();
        $arTag = [];
        foreach(explode(",",$arTagId) as $id) {
            $name = ScTag::findTagNameById($id);
            if ($name) {
                $arTag[] = $name;
            }
        }
        $formData = array(
            'article_id' => $article_model->getArticleId(),
            'article_order' => ($save_mode === ScLanguage::GENERAL) ? $data_post['article_order'] : $article_model->getArticleOrder(),
            'article_type_id' => ($save_mode === ScLanguage::GENERAL) ? $data_post['article_type_id'] : $article_model->getArticleTypeId(),
            'article_icon' => ($save_mode === ScLanguage::GENERAL) ? $data_post['article_icon'] : $article_model->getArticleIcon(),
            'article_active' => ($save_mode === ScLanguage::GENERAL) ? $data_post['article_active'] : $article_model->getArticleActive(),
            'article_is_home' => ($save_mode === ScLanguage::GENERAL) ? $data_post['article_is_home'] : $article_model->getArticleIsHome(),
            'article_tag'  => ($save_mode === ScLanguage::GENERAL) ? $data_post['article_tag'] : implode(";",$arTag),
            'arr_translate' => $arr_translate,
            'arr_language' => $arr_language,
            'lang_current' => $lang_current
        );
        $messages["status"] = "border-red";
        $select_type = Type::getParentIdType("", 0, $formData["article_type_id"]);
        $select_tag = Article::selectTag($ar_tag_id);
        $this->view->setVars([
            'formData' => $formData,
            'messages' => $messages,
            'select_type' => $select_type,
            'select_tag' => $select_tag
        ]);
    }

    public function deleteAction()
    {
        $items_checked = $this->request->getPost("item");


        if (!empty($items_checked)) {
            $msg_result = array();
            $count_delete = 0;
            foreach ($items_checked as $id) {
                $item = Article::findFirstById($id);
                if ($item) {
                    if ($item->delete() === false) {
                        $message_delete = 'Can\'t delete the Article Name = ' . $item->getArticleName();
                        $msg_result['status'] = 'error';
                        $msg_result['msg'] = $message_delete;
                    } else {
                        $count_delete++;
                        ArticleLang::deleteById($id);
                    }
                }
            }
            if ($count_delete > 0) {
                $message_delete = 'Delete ' . $count_delete . ' Article successfully.';
                $msg_result['status'] = 'success';
                $msg_result['msg'] = $message_delete;
            }
            $this->session->set('msg_result', $msg_result);
            return $this->response->redirect('/dashboard/list-article');
        }
    }

    private function getParameter($selectAll = '')
    {
        $lang = $this->request->get("slcLang", array('string', 'trim'));
        $keyword = trim($this->request->get("txtSearch"));
        $type = $this->request->get("slType");
        $langCode = !empty($lang) ? $lang : $this->globalVariable->defaultLanguage;
        $this->dispatcher->setParam("slcLang", $langCode);
        $match = trim($this->request->get("radMatch"));
        $arrParameter = [];
        if ($match == '') {
            $match = 'notmatch';
        }
        $this->dispatcher->setParam("radMatch", $match);

        $validator = new Validator();
        if ($langCode === $this->globalVariable->defaultLanguage) {
            $sql = "SELECT a.* FROM Score\Models\ScArticle as a 
            WHERE 1";
            if (!empty($keyword)) {
                if ($validator->validInt($keyword)) {
                    $sql .= " AND (a.article_id = :number:)";
                    $arrParameter['number'] = $keyword;
                } else {
                    if ($match == 'match') {
                        $sql .= " AND (a.article_name =:keyword: OR a.article_title =:keyword:
                                     OR a.article_meta_keyword =:keyword: OR a.article_meta_description =:keyword: 
                                     OR a.article_content =:keyword: OR a.article_meta_image =:keyword: OR a.article_summary =:keyword: 
                                    )";
                    } else {
                        $sql .= " AND (a.article_name like CONCAT('%',:keyword:,'%') OR a.article_title like CONCAT('%',:keyword:,'%')
                                     OR a.article_meta_keyword like CONCAT('%',:keyword:,'%') OR a.article_meta_description like CONCAT('%',:keyword:,'%')
                                     OR a.article_content like CONCAT('%',:keyword:,'%') OR a.article_summary like CONCAT('%',:keyword:,'%') 
                                     OR a.article_meta_image like CONCAT('%',:keyword:,'%')
                                     )";
                    }
                    $arrParameter['keyword'] = $keyword;
                }
                $this->dispatcher->setParam("txtSearch", $keyword);
            }
        } else {
            $sql = "SELECT a.*, al.* FROM Score\Models\ScArticle a 
                    INNER JOIN \Score\Models\ScArticleLang al
                                ON al.article_id = a.article_id AND  al.article_lang_code = :lang_code:                           
                    WHERE 1";
            $arrParameter['lang_code'] = $langCode;
            $this->dispatcher->setParam("slcLang", $langCode);
            if (!empty($keyword)) {
                if ($validator->validInt($keyword)) {
                    $sql .= " AND (a.article_id = :number:)";
                    $arrParameter['number'] = $keyword;
                } else {
                    if ($match == 'match') {
                        $sql .= " AND (al.article_name =:keyword: OR al.article_title =:keyword:
                                     OR al.article_meta_keyword =:keyword: OR al.article_meta_description =:keyword:
                                     OR al.article_content =:keyword: OR al.article_meta_image =:keyword: OR al.article_summary =:keyword: 
                                    )";
                    } else {
                        $sql .= " AND (al.article_name like CONCAT('%',:keyword:,'%') OR al.article_title like CONCAT('%',:keyword:,'%')
                                     OR al.article_meta_keyword like CONCAT('%',:keyword:,'%') OR al.article_meta_description like CONCAT('%',:keyword:,'%')
                                     OR al.article_content like CONCAT('%',:keyword:,'%') OR al.article_summary like CONCAT('%',:keyword:,'%') 
                                     OR al.article_meta_image like CONCAT('%',:keyword:,'%')
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
            $sql .= " AND a.article_type_id = :type_id:";
            $arrParameter["type_id"] = $type;
            $this->dispatcher->setParam("slType", $type);
        }
        $sql .= " ORDER BY a.article_id DESC";
        $data['para'] = $arrParameter;
        $data['sql'] = $sql;
        return $data;
    }
}
