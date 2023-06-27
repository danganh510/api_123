<?php

namespace Score\Backend\Controllers;

use Score\Models\ScUser;
use Score\Repositories\Country;
use Score\Repositories\Role;
use Score\Repositories\EmailTemplate;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Score\Repositories\User;
use Score\Utils\Validator;
use Score\Utils\NumVerify;
use Score\Utils\PasswordGenerator;
class UserController extends ControllerBase
{
    public function indexAction()
    {
        $data = $this->getParameter();
        $list_user = $this->modelsManager->executeQuery($data['sql'],$data['para']);
        $current_page = $this->request->get('page');
        $validator = new Validator();
        if($validator->validInt($current_page) == false || $current_page < 1)
            $current_page=1;
        $paginator = new PaginatorModel(
            array(
                'data'  => $list_user,
                'limit' => 20,
                'page'  => $current_page,
            )
        );
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
        $select_role = Role::getComboBox(isset($data["para"]["role"]) ? $data["para"]["role"] : "");
        $this->view->setVars(array(
            'user' => $paginator->getPaginate(),
            'msg_result'  => $msg_result,
            'msg_delete'  => $msg_delete,
            'select_role' => $select_role
        ));
    }
    public function editAction()
    {
        $id = $this->request->get('id');
        $checkID = new Validator();
        if(!$checkID->validInt($id))
        {
            $this->response->redirect('notfound');
            return ;
        }
        $user_model = ScUser::findFirstById($id);
        if(empty($user_model))
        {
            $this->response->redirect('notfound');
            return;
        }
        if ($this->session->has('msg_information')) {
            $msg_information = $this->session->get('msg_information');
            $this->session->remove('msg_information');
            $this->view->msg_information = $msg_information;
        }
        if ($this->session->has('messages')) {
            $messages = $this->session->get('messages');
            $this->session->remove('messages');
            $this->view->messages = $messages;
        }
        if ($this->session->has('input_data')) {
            $input_data = $this->session->get('input_data');
            $this->session->remove('input_data');
        }
        if ($this->session->has('msg_password')) {
            $msg_password = $this->session->get('msg_password');
            $this->session->remove('msg_password');
            $this->view->msg_password = $msg_password;
        }
        if ($this->session->has('msg_role')) {
            $msg_role = $this->session->get('msg_role');
            $this->session->remove('msg_role');
            $this->view->msg_role = $msg_role;
        }
        
        $data = array(
            'user_id' => $user_model->getUserId(),
            'user_email' => isset($input_data['user_email']) ? $input_data['user_email'] : $user_model->getUserEmail(),
            'user_name' => isset($input_data['user_name']) ? $input_data['user_name'] : $user_model->getUserName(),

            'user_role_id' => isset($input_data['user_role_id']) ? $input_data['user_role_id'] : $user_model->getUserRoleId(),
            'user_active' => isset($input_data['user_active']) ? $input_data['user_active'] : $user_model->getUserActive(),
            'user_insert_time' => $user_model->getUserInsertTime(),
        );
        $strRole = Role::getComboBox($data['user_role_id']);
        $select_country = Country::getComboboxByCode($data['user_country_code']);

        $this->view->setVars(array(
            'data' => $data,
            'slcRole' => $strRole,
            'select_country' => $select_country,
        ));
    }
    public function informationAction()
    {
        $id = $this->request->get('id');
        $checkID = new Validator();
        if(!$checkID->validInt($id))
        {
            $this->response->redirect('notfound');
            return ;
        }
        $user_model = ScUser::findFirstById($id);
        if($user_model === null)
        {
            $this->response->redirect('notfound');
            return;
        }
        $input_data = $user_model->toArray();
        $messages = array();
        if($this->request->isPost()) {
            $input_data['user_name'] = $this->request->getPost('txtName', array('string', 'trim'));
            $input_data['user_active'] = $this->request->getPost('radActive', array('string', 'trim'));
            
            if(empty($input_data['user_name'])) {
                $messages['user_name'] = 'First Name field is required.';
            }
          
            
            if(count($messages) == 0)
            {
                
                $result = $user_model->update($input_data);
                if ($result === false) {
                    $message = "Edit User fail !";
                    $msg_result['status'] = 'error';
                    $msg_result['msg'] = $message;
                } else {
                    
                    $msg_result = array('status' => 'success', 'msg' => 'Edit User Success');
                }
                $this->session->set('msg_information', $msg_result);
            } else {
                $messages['status'] = 'border-red';
                $this->session->set('input_data', $input_data);
                $this->session->set('messages', $messages);
            }
        }
        return $this->response->redirect("dashboard/list-user?id=".$id);
    }
    public function passwordAction()
    {
        $id = $this->request->get('id');
        $checkID = new Validator();
        if(!$checkID->validInt($id))
        {
            $this->response->redirect('notfound');
            return ;
        }
        $user_model = ScUser::findFirstById($id);
        if(empty($user_model))
        {
            $this->response->redirect('notfound');
            return;
        }
        if($this->request->isPost()) {
            $input_data = array(
                'user_id' => $id,
                'user_password' => $this->request->getPost('txtPassword', array('string', 'trim')),
            );
            $data = $input_data;
            $messages = array();
            if(empty($data['user_password'])) {
                $messages['password'] = 'New Password field is required.';
            }

            if(count($messages) == 0){
                $user_model->setUserPassword($data['user_password']);
                $result = $user_model->update();
                if ($result === false) {
                    $message = "Change Password fail !";
                    $msg_result['status'] = 'error';
                    $msg_result['msg'] = $message;
                } else {
                    $msg_result = array('status' => 'success', 'msg' => 'Change Password Success');
                }
                $this->session->set('msg_password', $msg_result);
            }
        }
        return $this->response->redirect("dashboard/list-user?id=".$id);
    }
    public function roleAction()
    {
        $id = $this->request->get('id');
        $checkID = new Validator();
        if(!$checkID->validInt($id))
        {
            $this->response->redirect('notfound');
            return ;
        }
        $user_model = ScUser::findFirstById($id);
        if(empty($user_model))
        {
            $this->response->redirect('notfound');
            return;
        }
        $data = array(
            'user_id' => $user_model->getUserId(),
            'user_role_id' => $user_model->getUserRoleId(),
        );
        if($this->request->isPost()) {
            $input_data = array(
                'user_id' => $id,
                'user_role_id' => $this->request->getPost('slcRole'),
            );
            $data = $input_data;
            $messages = array();
            if(count($messages) == 0){
              
                $user_model->setUserRoleId($data['user_role_id']);
                $result = $user_model->update();
                if ($result === false) {
                    $message = "Update Role fail !";
                    $msg_result['status'] = 'error';
                    $msg_result['msg'] = $message;
                } else {
                    $msg_result = array('status' => 'success', 'msg' => 'Update Role Success');

                }
                $this->session->set('msg_role', $msg_result);
            }
        }
        return $this->response->redirect("dashboard/list-user?id=".$id);
    }
    public function deleteAction ()
    {
        $list_user = $this->request->get('item');
        $Content_user = array();
        $msg_delete = array('error' => '', 'success' => '');
        if($list_user) {
            foreach ($list_user as $user_id) {
                $user_model = ScUser::findFirstById($user_id);
                if($user_model) {
                    $table_names = array();
                    $message_temp = "Can't delete Name = ".$user_model->getUserName().". Because It's exist in";
                    if(empty($table_names)) {
                        $old_user_data = $user_model->toArray();
                        $new_user_data = array();
                        $Content_user[$user_id] = array($old_user_data, $new_user_data);
                        $user_model->delete();
                    } else {
                        $msg_delete['error'] .= $message_temp.implode(",", $table_names)."<br>";
                    }
                }
            }
        }
        if (count($Content_user) > 0 ) {
            // delete success
            $message = 'Delete '. count($Content_user) .' user success.';
            $msg_delete['success'] = $message;

        }
        $this->session->set('msg_delete', $msg_delete);
        $this->response->redirect('/dashboard/list-user');
        return;
    }
    private function getParameter(){
        $sql = "SELECT *
                FROM Score\Models\ScUser
                WHERE 1";
        $appId = $this->request->get("slcAppId");
        $keyword = trim($this->request->get("txtSearch"));
        $from = trim($this->request->get("txtFrom")); //string
        $to = trim($this->request->get("txtTo"));  //string
        $active = trim($this->request->get("slcActive"));  //string
        $subscribe = trim($this->request->get("slcSubscribe"));  //string
        $role = trim($this->request->get("slcRole"));  //string
        $arrParameter = array();
        $validator = new Validator();
        if(!empty($keyword)) {
            if($validator->validInt($keyword)) {
                $sql.= " AND (user_id = :number:)";
                $arrParameter['number'] = $this->my->getIdFromFormatID($keyword, true);
            }
            else {
                $sql.=" AND (CONCAT(user_name ,' ',user_last_name) like CONCAT('%',:keyword:,'%') OR user_email like CONCAT('%',:keyword:,'%'))";
                $arrParameter['keyword'] = $keyword;
            }
            $this->dispatcher->setParam("txtSearch", $keyword);
        }
      
        if($from){
            $intFrom = $this->my->UTCTime(strtotime($from)); //UTC_mysql_time = date_picker - time zone
            $sql .= " AND user_insert_time >= :from:";
            $arrParameter['from'] = $intFrom;
            $this->dispatcher->setParam("txtFrom", $from);
        }
        if($to){
            $intTo = $this->my->UTCTime(strtotime($to)); //UTC_mysql_time = date_picker - time zone
            $sql .= " AND user_insert_time <= :to:";
            $arrParameter['to'] = $intTo;
            $this->dispatcher->setParam("txtTo", $to);
        }
        if($active){
            $sql .= " AND user_active = :active:";
            $arrParameter['active'] = $active;
            $this->dispatcher->setParam("slcActive", $active);
        }
       
        if($role){
            $sql .= " AND user_role_id = :role:";
            $arrParameter['role'] = $role;
            $this->dispatcher->setParam("slcRole", $role);
        }
        $sql.=" ORDER BY user_insert_time DESC";
        $data['para'] = $arrParameter;
        $data['sql'] = $sql;
        return $data;
    }
    private function getDataExportCSV($list_user) {
        $results[] = array("Id"
        ,"First Name","Last Name","Email","Tel","Country Code","Address","Avatar","Birthday"
        ,"Type","Gender","App ID","Api Number","Api Local Format","Api International Format"
        ,"Api Country Prefix","Api Country Code","Api Country Name","Api Location","Api Carrier"
        ,"Api Line Type","Role","Active","Insert Time");
        /**
         * @var ScUser $item
         */
        foreach ($list_user as $item)
        {
            $test = array(
                $this->my->formatUserID($item->getUserInsertTime(),$item->getUserId()),
                $item->getUserFirstName(),
                $item->getUserLastName(),
                $item->getUserEmail(),
                $item->getUserTel(),
                $item->getUserCountryCode(),
                $item->getUserAddress(),
                $item->getUserAvatar(),
                $item->getUserBirthday() != '' ?" ".$this->my->formatDateTime($item->getUserBirthday(),false)." " : "",
                $item->getUserType(),
                $item->getUserGender(),
                $item->getUserAppId(),
                $item->getUserTelapiNumber(),
                $item->getUserTelapiLocalFormat(),
                $item->getUserTelapiInternationalFormat(),
                $item->getUserTelapiCountryPrefix(),
                $item->getUserTelapiCountryCode(),
                $item->getUserTelapiCountryName(),
                $item->getUserTelapiLocation(),
                $item->getUserTelapiCarrier(),
                $item->getUserTelapiLineType(),
                Role::getNameRole($item->getUserRoleId()),
                $item->getUserActive(),
                " ".$this->my->formatDateTime($item->getUserInsertTime(),false)." ",
            );
            $results[] = $test;
        }
        return $results;
    }
    private function getDataExportCSVForMarketting($list_user) {
        $results[] = array("id","name","email",'insert time');
        foreach ($list_user as $item)
        {
            if ($item->getUserActive() == "N" || $item->getUserIsSubscribe() == "N" ) {
                continue;
            }
            $test = array(
                $item->UserId,
                $item->getUserFirstName() . ' '. $item->getUserLastName(),
                $item->getUserEmail(),
                $item->getUserInsertTime(),
            );
            $results[] = $test;
        }
        return $results;
    }
    private function getDataExportCSVForMarkettingVn($list_user) {
        $results[] = array("id","name","email",'insert time');
        foreach ($list_user as $item)
        {
            if ($item->getUserActive() == "N" || $item->getUserIsSubscribe() == "N" ) {
                continue;
            }
            if ($item->user_nationality == "VN") {
                $test = array(
                    $item->UserId,
                    $item->getUserFirstName() . ' '. $item->getUserLastName(),
                    $item->getUserEmail(),
                    $item->getUserInsertTime(),
                );
                $results[] = $test;
            }
        }
        return $results;
    }
    private function getDataExportCSVForMarkettingExceptVn($list_user) {
        $results[] = array("id","name","email",'insert time');
        foreach ($list_user as $item)
        {
            if ($item->getUserActive() == "N" || $item->getUserIsSubscribe() == "N" ) {
                continue;
            }
            if ($item->user_nationality == "VN") {
                continue;
            }
            $test = array(
                $item->UserId,
                $item->getUserFirstName() . ' '. $item->getUserLastName(),
                $item->getUserEmail(),
                $item->getUserInsertTime(),
            );
            $results[] = $test;
        }
        return $results;
    }
}