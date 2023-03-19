<?php
namespace Score\Backend\Controllers;

use Score\Repositories\Country;
use Score\Repositories\Match;
use Score\Models\ScMatch;
use Score\Utils\Validator;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Score\Repositories\MatchRepo;
use Score\Repositories\Team;
use Score\Repositories\Tournament;

class MatchController extends ControllerBase
{
    public function indexAction()
    {
        $current_page = $this->request->get('page');
        $validator = new Validator();
        if ($validator->validInt($current_page) == false || $current_page < 1)
            $current_page = 1;
        $keyword = trim($this->request->get("txtSearch"));
        $tournament = trim($this->request->get("slcTournament"));
        $team = trim($this->request->get("slcTeam"));
        $from = trim($this->request->get("txtFormDate"));
        $to = trim($this->request->get("txtToDate"));
        if (empty($keyword) && empty($tournament) && empty($team)) {
            $from = $from ? $from : $this->my->formatDateTime((time()) - 14 * 24 * 60 * 60);
        }

        $sql = ScMatch::query()->where("1");
        if (!empty($from)) {
            $from_search = strtotime($from) - $this->globalVariable->timeZone;
            $sql->andWhere(" match_start_time >= :formdate: ",["formdate" => $from_search]);
            $this->dispatcher->setParam("txtFormDate", $from);
        }
        if (!empty($to)) {
            $to_search = strtotime($to) - $this->globalVariable->timeZone;
            $sql->andWhere(" match_start_time <= :todate: ",["todate" => $to_search]);
            $this->dispatcher->setParam("txtToDate", $to);
        }
        if (!empty($keyword)) {
            if ($validator->validInt($keyword)) {
                $sql->andWhere("match_id = :keyword:",["keyword" => $keyword]);
            } else {
                $sql->andWhere("match_name like CONCAT('%',:keyword:,'%') ",["keyword" => $keyword]);
            }
            $this->dispatcher->setParam("txtSearch", $keyword);
        }
        if (!empty($tournament)) {
            $sql->andWhere("match_tournament_id = :tour_id:",["tour_id" => $tournament]);
            $this->dispatcher->setParam("slcTournament", $tournament);
        }
        if (!empty($team)) {
            $sql->andWhere("match_home_id = :team_id: OR match_away_id = :team_id:",["team_id" => $team]);
            $this->dispatcher->setParam("slcTeam", $team);
        }
       
        $sql->orderBy("match_id DESC");
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
        $slcTournament = Tournament::getCombobox($tournament);
        $slcTeam = Team::getCombobox($team);
        $this->view->setVars([
            'slcTournament' => $slcTournament,
            'slcTeam' => $slcTeam,
            'list_language' => $paginator->getPaginate()
        ]);
    }


    public function createAction()
    {
        $data = array('match_id' => -1,'match_active_mobile' => 'N',  'match_active' => 'Y', 'match_order' => 1,'match_is_translate_keyword' => 'N');
        if ($this->request->isPost()) {
            $messages = array();
            $data = array(
                'match_name' => $this->request->getPost("txtName", array('string', 'trim')),
                'match_code' => $this->request->getPost("txtCode", array('string', 'trim')),
                'match_code_time' => $this->request->getPost("txtCodeTime", array('string', 'trim')),
                'match_country_code' => $this->request->getPost("slcCountry", array('string', 'trim')),
                'match_order' => $this->request->getPost("txtOrder", array('string', 'trim')),
                'match_active_mobile' => $this->request->getPost("radActiveMobile"),
                'match_active' => $this->request->getPost("radActive"),
                'match_is_translate_keyword' => $this->request->getPost("radIsTranslateKeyword"),
            );
            if (empty($data["match_name"])) {
                $messages["name"] = "Name field is required.";
            }
            if ($data['match_code'] == "") {
                $messages['code'] = 'Code field is required.';
            } else if (Match::checkCode($data['match_code'], -1)) {
                $messages["code"] = "Code is exists.";
            }
            if (empty($data['match_order'])) {
                $messages["order"] = "Order field is required.";
            } else if (!is_numeric($data["match_order"])) {
                $messages["order"] = "Order is not valid ";
            }
            if (count($messages) == 0) {
                $msg_result = array();
                $new_language = new ScMatch();
                if ($new_language->save($data)) {
                    $msg_result = array('status' => 'success', 'msg' => 'Create Match Success');
                } else {
                    $message = "We can't store your info now: \n";
                    foreach ($new_language->getMessages() as $msg) {
                        $message .= $msg . "\n";
                    }
                    $msg_result['status'] = 'error';
                    $msg_result['msg'] = $message;
                }

                $this->session->set('msg_result', $msg_result);
                return $this->response->redirect("/language");
            }
        }
        $select_country = Country::getComboboxByCode($data['match_country_code']);
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
        $match_model = MatchRepo::getFirstById($id);
        if (empty($match_model)) {
            return $this->response->redirect('notfound');
        }
        $data = $match_model->toArray();
        $messages = array();
        if ($this->request->isPost()) {
            foreach ($_POST as $key => $value) {
                if ($key == "match_start_time") {
                    $data[$key] = strtotime($value) - $this->globalVariable->timeZone;

                } else {
                    $data[$key] = $value;

                }
            }
            if (empty($data["match_name"])) {
                $messages["name"] = "Name field is required.";
            }
            if (count($messages) == 0) {
                $msg_result = array();
                if ($match_model->update($data)) {
                    $msg_result = array('status' => 'success', 'msg' => 'Edit match Success');
                } else {
                    $message = "We can't store your info now: \n";
                    foreach ($match_model->getMessages() as $msg) {
                        $message .= $msg . "\n";
                    }
                    $msg_result['status'] = 'error';
                    $msg_result['msg'] = $message;
                }
                $this->session->set('msg_result', $msg_result);
                return $this->response->redirect("/dashboard/list-match");
            }
        }
        $messages["status"] = "border-red";
        $this->view->setVars([
            'formData' => $data,
            'messages' => $messages,
        ]);
    }

    public function deleteAction()
    {
        $match_checked = $this->request->getPost("item");
        if (!empty($match_checked)) {
            $tn_log = array();
            foreach ($match_checked as $id) {
                $match_item = MatchRepo::getFirstById($id);
                if ($match_item) {
                    $msg_result = array();
                    if ($match_item->delete() === false) {
                        $message_delete = 'Can\'t delete the Match Name = ' . $match_item->getMatchName();
                        $msg_result['status'] = 'error';
                        $msg_result['msg'] = $message_delete;
                    } else {
                        $tn_log[$id] = $match_item->toArray();
                    }
                }
            }
            if (count($tn_log) > 0) {
                $message_delete = 'Delete ' . count($tn_log) . ' Match successfully.';
                $msg_result['status'] = 'success';
                $msg_result['msg'] = $message_delete;
            }
            $this->session->set('msg_result', $msg_result);
            return $this->response->redirect("/dashboard/list-match");
        }
    }
}
