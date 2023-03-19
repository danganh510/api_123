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
            'user_tel' => isset($input_data['user_tel']) ? $input_data['user_tel'] : $user_model->getUserTel(),
            'user_country_code' => isset($input_data['user_country_code']) ? $input_data['user_country_code'] : $user_model->getUserCountryCode(),
            'user_telapi_international_format' => isset($input_data['user_telapi_international_format']) ? $input_data['user_telapi_international_format'] : $user_model->getUserTelapiInternationalFormat(),
            'user_telapi_country_code' => isset($input_data['user_telapi_country_code']) ? $input_data['user_telapi_country_code'] : $user_model->getUserTelapiCountryCode(),

            'user_address' => isset($input_data['user_address']) ? $input_data['user_address'] : $user_model->getUserAddress(),
            'user_payment_fails' => isset($input_data['user_payment_fails']) ? $input_data['user_payment_fails'] : $user_model->getUserPaymentFails(),
            'user_role_id' => isset($input_data['user_role_id']) ? $input_data['user_role_id'] : $user_model->getUserRoleId(),
            'user_active' => isset($input_data['user_active']) ? $input_data['user_active'] : $user_model->getUserActive(),
            'user_is_subscribe' => isset($input_data['user_is_subscribe']) ? $input_data['user_is_subscribe'] : $user_model->getUserIsSubscribe(),
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
        $messages = array();
        if($this->request->isPost()) {
            $input_data = array(
                'user_id' => $id,
                'user_first_name' => $this->request->getPost('txtFirstName', array('string', 'trim')),
                'user_second_name' => $this->request->getPost('txtSecondName', array('string', 'trim')),
                'user_last_name' => $this->request->getPost('txtLastName', array('string', 'trim')),
                'user_email' => $this->request->getPost('txtEmail', array('string', 'trim')),
                'user_country_code' => $this->request->getPost('slcCountry', array('string', 'trim')),
                'user_currency_code' => $this->request->getPost('slcCurrency', array('string', 'trim')),
                'user_language_code' => $this->request->getPost('slcLanguage', array('string', 'trim')),
                'user_address' => $this->request->getPost('txtAddress', array('string', 'trim')),
                'user_avatar' => $this->request->getPost('txtAvatar', array('string', 'trim')),
                'user_birthday' => $this->request->getPost('txtBirthday', array('string', 'trim')),
                'user_type' => $this->request->getPost('slcType'),
                'user_gender' => $this->request->getPost('slcGender'),
                'user_app_id' => $this->request->getPost('txtApp', array('string', 'trim')),
                'user_payment_fails' => $this->request->getPost('txtPayment', array('string', 'trim')),
                'user_insert_time' => $this->globalVariable->curTime,
                'user_active' => $this->request->getPost('radActive'),
                'user_is_subscribe' => $this->request->getPost('radSubscribe'),
                'user_tel' => $this->request->getPost('txtTel2', array('string', 'trim')),
                'user_telapi_country_code' => $this->request->getPost('txtCountryCode', array('string', 'trim')),
                'reason' => $this->request->getPost('txtReason', array('string', 'trim')),
            );
            if(empty($input_data['user_first_name'])) {
                $messages['user_first_name'] = 'First Name field is required.';
            }
            if(empty($input_data['user_last_name'])) {
                $messages['user_last_name'] = 'Last Name field is required.';
            }
            if(empty($input_data['user_tel'])) {
                $messages['user_tel'] = 'Tel field is required.';
            }
            if(empty($input_data['user_type'])) {
                $messages['user_type'] = 'Type field is required.';
            }
            if(empty($input_data['user_gender'])) {
                $messages['user_gender'] = 'Gender field is required.';
            }
            if ($input_data['user_payment_fails'] === '' ) {
                $messages['user_payment_fails'] = "Payment Fails field is required.";
            } else if (!is_numeric($input_data['user_payment_fails']) ){
                $messages['user_payment_fails'] = "Payment Fails is not valid";
            }
            $user_tel_info = NumVerify::info($input_data['user_tel']);
            if ($user_tel_info->valid != true) {
                $messages['user_tel'] = "Invalid number";
            } else {
                $input_data = array_merge($input_data, array(
                    'user_telapi_number' => $user_tel_info->number,
                    'user_telapi_local_format' => $user_tel_info->local_format,
                    'user_telapi_international_format' => $user_tel_info->international_format,
                    'user_telapi_country_prefix' => $user_tel_info->country_prefix,
                    'user_telapi_country_code' => $user_tel_info->country_code,
                    'user_telapi_country_name' => $user_tel_info->country_name,
                    'user_telapi_location' => $user_tel_info->location,
                    'user_telapi_carrier' => $user_tel_info->carrier,
                    'user_telapi_line_type' => $user_tel_info->line_type,
                ));
            }
            if(empty($input_data['user_country_code'])) {
                $messages['user_country_code'] = 'Country field is required.';
            }

            if(count($messages) == 0)
            {
                $flag_sent_email_update = false;
                $arrReason = json_decode($user_model->getUserReason(), true);
                
                //active lại tài khoản
                if ($input_data['user_active'] != $user_model->getUserActive()) {
                    if ($input_data['user_active'] == "Y") {
                        $arrReason[] = [
                            'time' => time(),
                            'action' => "activeUser",
                            'userUpdate' => $this->auth['id'],
                            'message' => $input_data['reason']
                        ];
                        $flag_sent_email_update = true;
                    } else {
                        $arrReason[] = [
                            'time' => time(),
                            'action' => "updateUser",
                            'userUpdate' => $this->auth['id'],
                            'message' => $input_data['reason']
                        ];
                    }
                } else {
                    $arrReason[] = [
                        'time' => time(),
                        'action' => "updateUser",
                        'userUpdate' => $this->auth['id'],
                        'message' => $input_data['reason']
                    ];
                }

                $input_data['user_reason'] = json_encode($arrReason);
                $input_data['user_birthday'] = strtotime($input_data['user_birthday']);
                $result = $user_model->update($input_data);
                if ($result === false) {
                    $message = "Edit User fail !";
                    $msg_result['status'] = 'error';
                    $msg_result['msg'] = $message;
                } else {
                    if ($flag_sent_email_update == true) {
                        $userRepo = new User();
                        $userRepo->sentEmailActive($user_model);
                    }
                    $msg_result = array('status' => 'success', 'msg' => 'Edit User Success');
                }
                $this->session->set('msg_information', $msg_result);
            } else {
                $messages['status'] = 'border-red';
                $this->session->set('input_data', $input_data);
                $this->session->set('messages', $messages);
            }
        }
        return $this->response->redirect("user/edit?id=".$id);
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

            $arrReason = json_decode($user_model->getUserReason(), true);
            $arrReason[] = [
                'time' => time(),
                'action' => "changePassword",
                'userUpdate' => $this->auth['id'],
                'message' => $input_data['reason']
            ];
            if(count($messages) == 0){
                $user_model->setUserPassword($data['user_password']);
                $user_model->setUserReason(json_encode($arrReason));
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
        return $this->response->redirect("user/edit?id=".$id);
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
                $arrReason = json_decode($user_model->getUserReason(), true);
                $arrReason[] = [
                    'time' => time(),
                    'action' => "changeRole",
                    'userUpdate' => $this->auth['id'],
                    'message' => $input_data['reason']
                ];
                $user_model->setUserRoleId($data['user_role_id']);
                $user_model->setUserReason(json_encode($arrReason));
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
        return $this->response->redirect("user/edit?id=".$id);
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
                    $message_temp = "Can't delete Name = ".$user_model->getUserFirstName()." ".$user_model->getUserLastName().". Because It's exist in";
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
        $this->response->redirect('/user');
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
                $sql.=" AND (CONCAT(user_first_name ,' ',user_last_name) like CONCAT('%',:keyword:,'%') OR user_email like CONCAT('%',:keyword:,'%'))";
                $arrParameter['keyword'] = $keyword;
            }
            $this->dispatcher->setParam("txtSearch", $keyword);
        }
        if($appId){
            $sql .= " AND user_app_id = :appId:";
            $arrParameter['appId'] = $appId;
            $this->dispatcher->setParam("slcAppId", $appId);
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
        if($subscribe){
            $sql .= " AND user_is_subscribe = :subscribe:";
            $arrParameter['subscribe'] = $subscribe;
            $this->dispatcher->setParam("slcSubscribe", $subscribe);
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