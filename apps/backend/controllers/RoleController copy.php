<?php

namespace Score\Backend\Controllers;

use Score\Repositories\User;
use Score\Models\ScRole;
use Score\Utils\Validator;
use Score\Repositories\Role;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;

class RoleController extends ControllerBase
{

    public function indexAction()
    {
        $current_page = $this->request->get('page');
        $validator = new Validator();
        if ($validator->validInt($current_page) == false || $current_page < 1)
            $current_page = 1;

        $list_role = $this->getParameter();

        $paginator = new PaginatorModel(
            [
                'data' => $list_role,
                'limit' => 20,
                'page' => $current_page,
            ]
        );
        $msg_info = array();
        if ($this->session->has('msg_info')) {
            $msg_info = $this->session->get('msg_info');
            $this->session->remove('msg_info');
        }
        $msg_result = array();
        if ($this->session->has('msg_del')) {
            $msg_result = $this->session->get('msg_del');
            $this->session->remove('msg_del');;
        }
        $this->view->setVars(array(
            'list_role' => $paginator->getPaginate(),
            'msg_info' => $msg_info,
            'msg_del' => $msg_result
        ));
    }

    public function createAction()
    {
        $input_data = array('id' => 0, 'active' => 'Y', 'order' => '1', 'actions' => array());
        $msg_info = array();
        if ($this->request->isPost()) {
            $name = $this->request->getPost('txtName', array('string', 'trim'));
            $order = $this->request->getPost('txtOrder', array('string', 'trim'));
            $active = $this->request->getPost('rdActive');
            $actions = $this->getActions();
            $input_data = array(
                'name' => $name,
                'order' => $order,
                'active' => $active,
                'actions' => $actions
            );
            $validator = new Validator();
            if ($name == '') {
                $msg_info['name'] = 'Name field is required.';
            } else {
                $name_exist = Role::getByName($name, 0);
                if ($name_exist || in_array($name, Role::getGuestUser())) $msg_info['name'] = 'Name "' . $name . '" is exists.';
            }
            if ($order == '') {
                $msg_info['order'] = 'Order field is required.';
            } else if (!$validator->validInt($order)) {
                $msg_info['order'] = 'Enter a valid order.';
            }
            if (count($msg_info) == 0) {
                $new_role = new ScRole();
                $new_role->setRoleName($name);
                $new_role->setRoleOrder($order);
                $new_role->setRoleActive($active);
                $new_role->setRoleFunction(serialize($actions));

                $msg_info = array('status' => 'error', 'message' => 'Create a role fail.');
                if ($new_role->save()) {
                    $msg_info = array('status' => 'success', 'message' => 'Create a role with ID = ' . $new_role->getRoleId() . ' success.');
                }
                $this->session->set('msg_info', $msg_info);
                $this->response->redirect('/role');
                return;
            }
        }
        $role = new Role();
        $input_data["str_action"] = $role->getFunctionRole(4, $this->getArrDirectory(), $input_data['actions'], 'backend');
        $arr_role = array();
        $arr_role['input_data'] = $input_data;
        $arr_role['msg_info'] = $msg_info;
        $this->view->arr_role = $arr_role;
    }

    public function editAction()
    {
        $id = $this->request->get('id');
        $validator = new Validator();
        if (!$validator->validInt($id)) {
            $this->view->disable();
            $this->response->redirect('notfound');
            return;
        }
        $arr_role = array();
        $msg_info = array();
        $arr_role['arr_dir'] = $this->getArrDirectory();
        $role_edit = Role::getByID($id);
        if ($role_edit) {
            $model_data = array(
                'id' => $id,
                'name' => $role_edit->getRoleName(),
                'order' => $role_edit->getRoleOrder(),
                'active' => $role_edit->getRoleActive(),
                'actions' => unserialize($role_edit->getRoleFunction())
            );
            $input_data = $model_data;
            if ($this->request->isPost()) {
                $name = $this->request->getPost('txtName', array('string', 'trim'));
                $order = $this->request->getPost('txtOrder', array('string', 'trim'));
                $active = $this->request->getPost('rdActive');
                $actions = $this->getActions();

                $input_data = array(
                    'id' => $id,
                    'name' => $name,
                    'order' => $order,
                    'active' => $active,
                    'actions' => $actions
                );
                if ($name == '') {
                    $msg_info['msg_name'] = 'Name field is required.';
                } else {
                    $name_exist = Role::getByName($name, $id);
                    if ($name_exist) $msg_info['msg_name'] = 'Name "' . $name . '" is exists.';
                }
                if ($order == '') {
                    $msg_info['msg_order'] = 'Order field is required.';
                } else if (!$validator->validInt($order)) {
                    $msg_info['msg_order'] = 'Enter a valid order.';
                }
                if (count($msg_info) == 0) {
                    $role_edit->setRoleName($name);
                    $role_edit->setRoleOrder($order);
                    $role_edit->setRoleActive($active);
                    $role_edit->setRoleFunction(serialize($actions));

                    $msg_info = array('status' => 'danger', 'message' => 'Edit a role with ID = ' . $id . ' fail.');

                    if ($role_edit->save()) {
                        $msg_info = array('status' => 'success', 'message' => 'Edit a role with ID = ' . $id . ' success.');
                        if ($name == $this->auth['role']) {
                            $msg_info['session_destroy'] = true;
                        }
                    }
                    $this->session->set('msg_info', $msg_info);
                    $this->response->redirect('/role');
                }
            }
            $role = new Role();
            $input_data["str_action"] = $role->getFunctionRole(4, $this->getArrDirectory(), $input_data['actions'], 'backend');
            $arr_role = array();
            $arr_role['input_data'] = $input_data;
            $arr_role['msg_info'] = $msg_info;
            $this->view->arr_role = $arr_role;
        } else {
            $this->response->redirect('notfound');
            return;
        }
    }

    public function deleteAction()
    {
        $role_checks = $this->request->getPost("item");
        if ($role_checks) {
            $messages = array('error' => '',
                'success' => '');
            $data_log = array();
            $count = 0;
            foreach ($role_checks as $role_id) {
                $role_item = Role::getFirstById($role_id);
                if ($role_item) {
                    $user = User::findFirstByRole($role_id);
                    if ($user) {
                        $message = 'Can\'t delete the Role Name = ' . $role_item->getRoleName() . '. Because It\'s exist in User.<br>';
                        $messages['error'] .= $message;
                    } else {
                        $old_data = array(
                            'name' => $role_item->getRoleName(),
                            'order' => $role_item->getRoleOrder(),
                            'active' => $role_item->getRoleActive(),
                            'actions' => unserialize($role_item->getRoleFunction())
                        );
                        $new_data = array();
                        $dat_log[$role_id] = array($old_data, $new_data);
                        $role_item->delete();
                        $count++;
                    }
                }
            }
            if ($count > 0) {
                $messages['success'] = 'Delete ' . $count . ' role successfully.';
            }
            $this->session->set('msg_del', $messages);
            $this->response->redirect('/role');
            return;
        }
    }

    private function getParameter()
    {
        $sql = ScRole::query();
        $keyword = trim($this->request->get("txtSearch"));
        if (!empty($keyword)) {
            $sql->where("role_id = :keyword: OR role_name like CONCAT('%',:keyword:,'%')", ["keyword" => $keyword]);
            $this->dispatcher->setParam("txtSearch", $keyword);
        }
        return $sql->execute();
    }

    // get Array Directory
    private function getArrDirectory()
    {
        $arr_dir = array();
        $directory_backend = __DIR__ . "/../../backend/controllers/*.php";
        foreach (glob($directory_backend) as $controller) {
            $className = 'Score\Backend\Controllers\\' . basename($controller, '.php');
            $className2 = basename($controller, 'Controller.php');
            if (!strpos($className2, '.php')) {
                $parent_name = lcfirst($className2);
                $key = 'backend' . $parent_name;
                if (empty($arr_dir[$key])) $arr_dir[$key] = array();
                $methods = (new \ReflectionClass($className))->getMethods(\ReflectionMethod::IS_PUBLIC);
                foreach ($methods as $method) {
                    if (\Phalcon\Text::endsWith($method->name, 'Action')) {
                        $action = basename($method->name, 'Action');
                        $arr_dir[$key][] = $action;
                    }
                }
            }
        }
        return $arr_dir;
    }

    // get Actions
    private function getActions()
    {
        $resources = $this->getArrDirectory();
        $result = Role::getActions();
        foreach ($resources as $key => $values) {
            if (empty($result[$key])) $result[$key] = array();
            if (!empty($_POST[$key])) {
                for ($i = 0; $i < count($_POST[$key]); $i++) {
                    $result[$key][] = $_POST[$key][$i];
                }
            }
            if (count($result[$key]) == 0) {
                $result[$key][] = "temp";
            }
        }
        return $result;
    }
}