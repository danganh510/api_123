<?php

namespace Score\Backend\Controllers;

use Score\Repositories\Country;
use Score\Models\ScTeam;
use Score\Utils\Validator;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Score\Models\ScLanguage;
use Score\Models\ScTeamLang;
use Score\Repositories\Language;
use Score\Repositories\Team;

class TeamController extends ControllerBase
{
    public function indexAction()
    {
        $current_page = $this->request->get('page');
        $validator = new Validator();
        if ($validator->validInt($current_page) == false || $current_page < 1)
            $current_page = 1;
        $keyword = trim($this->request->get("txtSearch"));
        $sql = ScTeam::query();
        if (!empty($keyword)) {
            if ($validator->validInt($keyword)) {
                $sql->where("team_id = :keyword:", ["keyword" => $keyword]);
            } else {
                $sql->where("team_name like CONCAT('%',:keyword:,'%') OR team_name_livescore like CONCAT('%',:keyword:,'%') OR team_name_flashscore like CONCAT('%',:keyword:,'%')", ["keyword" => $keyword]);
            }
            $this->dispatcher->setParam("txtSearch", $keyword);
        }
        $sql->orderBy("team_id DESC");
        $list_tournament = $sql->execute();
        $paginator = new PaginatorModel(array(
            'data' => $list_tournament,
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
        $this->view->list_tournament = $paginator->getPaginate();
    }
    public function editAction()
    {
        $team_id = $this->request->get('id');
        $lang_current = $this->request->get('slcLang');
        $lang_current = $lang_current ? $lang_current : $this->globalVariable->defaultLanguage;
        $arr_language = Language::arrLanguages();
        if (!in_array($lang_current, array_keys($arr_language))) {
            return $this->response->redirect('notfound');
        }

        $checkID = new Validator();
        if (!$checkID->validInt($team_id)) {
            $this->response->redirect('notfound');
            return;
        }
        $team_model = Team::findFirstById($team_id);
        if (empty($team_model)) {
            $this->response->redirect('notfound');
            return;
        }
        $arr_translate = array();
        $messages = array();
        $data_post = $team_model->toArray();
        $save_mode = '';


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
                $data_post['team_name'] = trim($this->request->getPost('txtName'));
                $data_post['team_slug'] = trim($this->request->getPost('txtSlug'));

                if (empty($data_post['team_name'])) {
                    $messages[$save_mode]['team_name'] = 'Name field is required.';
                }
                if (empty($data_post['team_slug'])) {
                    $messages[$save_mode]['team_slug'] = 'Slug field is required.';
                }
            } else {
                if ($this->request->getPost('txtCountryCode', array('string', 'trim'))) {
                    $data_post['team_country_code'] = $this->request->getPost('txtCountryCode', array('string', 'trim'));
                }
                if ($this->request->getPost('txtFlashScore', array('string', 'trim'))) {
                    $data_post['team_name_flashscore '] = $this->request->getPost('txtFlashScore', array('string', 'trim'));
                }
                if ($this->request->getPost('txtLiveScore', array('string', 'trim'))) {
                    $data_post['team_name_livescore'] = $this->request->getPost('txtLiveScore', array('string', 'trim'));
                }
                if ($this->request->getPost("txtLogoMedium")) {
                    $data_post['team_logo_medium'] = $this->request->getPost("txtLogoMedium");
                }
                // $data_post['team_active'] = trim($this->request->get("txtTag"));


                if (empty($data_post["team_country_code"])) {
                    $messages["team_country_code"] = "Country code field is required.";
                }
            }

            if (empty($messages)) {
                switch ($save_mode) {
                    case ScLanguage::GENERAL:

                        $result = $team_model->update($data_post);

                        $info = ScLanguage::GENERAL;
                        break;
                    case $this->globalVariable->defaultLanguage:
                        $result = $team_model->update($data_post);

                        $info = $arr_language[$save_mode];
                        break;
                    default:
                        $team_lang_model = ScTeamLang::findFirstByIdAndLang($team_id, $save_mode);
                        if (!$team_lang_model) {
                            $team_lang_model = new ScTeamLang();
                            $team_lang_model->setTeamId($team_id);
                            $team_lang_model->setTeamLangCode($save_mode);
                        }
                        $team_lang_model->setTeamName($data_post['team_name']);
                        $team_lang_model->setTeamSlug($data_post['team_slug']);

                        $result = $team_lang_model->save();
                        $info = $arr_language[$save_mode];
                        break;
                }
                if ($result) {


                    $messages = array(
                        'message' => ucfirst($info . " Update Team success"),
                        'typeMessage' => "success",
                    );
                } else {
                    $messages = array(
                        'message' => "Update Team fail",
                        'typeMessage' => "error",
                    );
                }
            }
        }

        $team_model = Team::findFirstById($team_model->getTeamId());
        $item = array(
            'team_id' => $team_model->getTeamId(),
            'team_slug' => ($save_mode === $this->globalVariable->defaultLanguage) ? $data_post['team_slug'] : $team_model->getTeamSlug(),
            'team_name' => ($save_mode === $this->globalVariable->defaultLanguage) ? $data_post['team_name'] : $team_model->getTeamName(),
        );
        $arr_translate[$this->globalVariable->defaultLanguage] = $item;
        $arr_team_lang = ScTeamLang::findById($team_id);
        foreach ($arr_team_lang as $team_lang) {
            $item = array(
                'team_id' => $team_lang->getTeamId(),
                'team_slug' => ($save_mode === $team_lang->getTeamSlug()) ? $data_post['team_slug'] : $team_lang->getTeamSlug(),
                'team_name' => ($save_mode === $team_lang->getTeamName()) ? $data_post['team_name'] : $team_lang->getTeamName(),
            );
            $arr_translate[$team_lang->getTeamLangCode()] = $item;
        }
        if (!isset($arr_translate[$save_mode]) && isset($arr_language[$save_mode])) {
            $item = array(
                'team_id' => -1,
                'team_slug' => $data_post['team_slug'],
                'team_name' => $data_post['team_name'],
            );
            $arr_translate[$save_mode] = $item;
        }

        $formData = array(
            'team_id' => $team_model->getTeamId(),
            'team_country_code' => ($save_mode === ScLanguage::GENERAL) ? $data_post['team_country_code'] : $team_model->getTeamCountryCode(),
            'team_name_flashscore' => ($save_mode === ScLanguage::GENERAL) ? $data_post['team_name_flashscore'] : $team_model->getTeamNameFlashscore(),
            'team_logo_medium' => ($save_mode === ScLanguage::GENERAL) ? $data_post['team_logo_medium'] : $team_model->getTeamLogoMedium(),
            'team_name_livescore' => ($save_mode === ScLanguage::GENERAL) ? $data_post['team_name_livescore'] : $team_model->getTeamNameLivescore(),
            'arr_translate' => $arr_translate,
            'arr_language' => $arr_language,
            'lang_current' => $lang_current
        );
        $messages["status"] = "border-red";
        $select_country = Country::getComboboxAndAreaByCode($formData['team_country_code']);

        $this->view->setVars([
            'formData' => $formData,
            'messages' => $messages,
            'select_country' => $select_country
        ]);
        switch ($this->auth['role']) {
            case "Admin":
                $this->view->pick("team/edit-admin");
                break;
            case "data":
                $this->view->pick("team/edit-operator");
                break;
            case "crawler":
                $this->view->pick("team/edit-crawl");
                break;
        }
    }

    public function deleteAction()
    {
        $team_checked = $this->request->getPost("item");
        if (!empty($team_checked)) {
            $tn_log = array();
            foreach ($team_checked as $id) {
                $team_item = Team::getTeamById($id);
                if ($team_item) {
                    $msg_result = array();
                    if ($team_item->delete() === false) {
                        $message_delete = 'Can\'t delete the Team Name = ' . $team_item->getTeamName();
                        $msg_result['status'] = 'error';
                        $msg_result['msg'] = $message_delete;
                    } else {
                        $tn_log[$id] = $team_item->toArray();
                    }
                }
            }
            if (count($tn_log) > 0) {
                $message_delete = 'Delete ' . count($tn_log) . ' Team successfully.';
                $msg_result['status'] = 'success';
                $msg_result['msg'] = $message_delete;
            }
            $this->session->set('msg_result', $msg_result);
            return $this->response->redirect("/dashboard/list-team");
        }
    }
}
