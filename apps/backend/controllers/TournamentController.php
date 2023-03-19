<?php

namespace Score\Backend\Controllers;

use Score\Repositories\Country;
use Score\Models\ScTournament;
use Score\Utils\Validator;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Score\Repositories\Tournament;

class TournamentController extends ControllerBase
{
    public function indexAction()
    {
        $current_page = $this->request->get('page');
        $validator = new Validator();
        if ($validator->validInt($current_page) == false || $current_page < 1)
            $current_page = 1;
        $keyword = trim($this->request->get("txtSearch"));
        $sql = ScTournament::query();
        if (!empty($keyword)) {
            if ($validator->validInt($keyword)) {
                $sql->where("tournament_id = :keyword:", ["keyword" => $keyword]);
            } else {
                $sql->where("tournament_name like CONCAT('%',:keyword:,'%') ");
            }
            $this->dispatcher->setParam("txtSearch", $keyword);
        }
        $sql->orderBy("tournament_name");
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
        $tournament_model = Tournament::findFirstById($id);
        if (empty($tournament_model)) {
            return $this->response->redirect('notfound');
        }
        $data = $tournament_model->toArray();
        $messages = array();
        if ($this->request->isPost()) {
            foreach ($_POST as $key => $value) {
                if ($key == "match_start_time") {
                    $data[$key] = strtotime($value);
                } else {
                    $data[$key] = $value;
                }
            }
            if (empty($data["tournament_name"])) {
                $messages["name"] = "Name field is required.";
            }
         
            if (empty($data['tournament_order'])) {
                $messages["order"] = "Order field is required.";
            } else if (!is_numeric($data["tournament_order"])) {
                $messages["order"] = "Order is not valid ";
            }
            if (count($messages) == 0) {
                $msg_result = array();
                if ($tournament_model->update($data)) {
                    $msg_result = array('status' => 'success', 'msg' => 'Edit tournament Success');
                } else {
                    $message = "We can't store your info now: \n";
                    foreach ($tournament_model->getMessages() as $msg) {
                        $message .= $msg . "\n";
                    }
                    $msg_result['status'] = 'error';
                    $msg_result['msg'] = $message;
                }
                $this->session->set('msg_result', $msg_result);
                return $this->response->redirect("/dashboard/list-tournament");
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
        $tournament_checked = $this->request->getPost("item");
        if (!empty($tournament_checked)) {
            $tn_log = array();
            foreach ($tournament_checked as $id) {
                $tournament_item = Tournament::findFirstById($id);
                if ($tournament_item) {
                    $msg_result = array();
                    if ($tournament_item->delete() === false) {
                        $message_delete = 'Can\'t delete the Tournament Name = ' . $tournament_item->getTournamentName();
                        $msg_result['status'] = 'error';
                        $msg_result['msg'] = $message_delete;
                    } else {
                        $tn_log[$id] = $tournament_item->toArray();
                    }
                }
            }
            if (count($tn_log) > 0) {
                $message_delete = 'Delete ' . count($tn_log) . ' Tournament successfully.';
                $msg_result['status'] = 'success';
                $msg_result['msg'] = $message_delete;
            }
            $this->session->set('msg_result', $msg_result);
            return $this->response->redirect("/dashboard/list-tournament");
        }
    }
}
