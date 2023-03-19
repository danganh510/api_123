<?php

namespace Score\Backend\Controllers;

use Score\Repositories\Country;
use Score\Models\ScTeam;
use Score\Utils\Validator;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
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
                $sql->where("team_name like CONCAT('%',:keyword:,'%') ");
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
        $id = $this->request->get('id');
        $checkID = new Validator();
        if (!$checkID->validInt($id)) {
            return $this->response->redirect('notfound');
        }
        $team_model = Team::getTeamById($id);
        if (empty($team_model)) {
            return $this->response->redirect('notfound');
        }
        $data = $team_model->toArray();
        $messages = array();
        if ($this->request->isPost()) {
            foreach ($_POST as $key => $value) {
                if ($key == "match_start_time") {
                    $data[$key] = strtotime($value);
                } else {
                    $data[$key] = $value;
                }
            }
            if (empty($data["team_name"])) {
                $messages["name"] = "Name field is required.";
            }
         
            if (count($messages) == 0) {
                $msg_result = array();
                if ($team_model->update($data)) {
                    $msg_result = array('status' => 'success', 'msg' => 'Edit tournament Success');
                } else {
                    $message = "We can't store your info now: \n";
                    foreach ($team_model->getMessages() as $msg) {
                        $message .= $msg . "\n";
                    }
                    $msg_result['status'] = 'error';
                    $msg_result['msg'] = $message;
                }
                $this->session->set('msg_result', $msg_result);
                return $this->response->redirect("/dashboard/list-team");
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
