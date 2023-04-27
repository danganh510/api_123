<?php

namespace Score\Backend\Controllers;

use Score\Repositories\Role;

/**
 * @property \GlobalVariable globalVariable
 * @property \My my
 */
class ControllerBase extends \Phalcon\Mvc\Controller
{
    protected $auth;


    public function initialize()
    {
        //    date_default_timezone_set('Atlantic/Canary');

        header('Access-Control-Allow-Origin: *');

        $this->auth = $this->session->get('auth');
        if (isset($this->auth['role'])) {
            $role_function = array();
            if ($this->session->has('action')) {
                $role_function = $this->session->get('action');
            } else {
                $role = Role::getFirstByName($this->auth['role']);
                if ($role) {
                    $role_function = unserialize($role->getRoleFunction());
                    $this->session->set('action', $role_function);
                }
            }
        }

        $this->view->setVars([
            'role_function' => isset($role_function) ? $role_function : []
        ]);
    }
    public function checkTimeList()
    {
        //7h tới 11h tối thứ 7 cn tắt detail
        $dayOfWeek = date('N', time()); // Lấy số thứ tự của ngày trong tuần
        $currentHour = date('G');
        echo "Today is: " . $dayOfWeek . " and " . $currentHour . " Hour \r\n";
        if ($currentHour >= 11 && $currentHour <= 16) {
            if ($dayOfWeek != 6 && $dayOfWeek != 7) {
                $second = $time = date("s", time());
                if (($second >= 26 && $second <= 32) || ($second >= 46 && $second <= 52)) {
                    echo "gio cao diem";
                    die();
                }
            }
        }
        // echo "wait update";
        // die();
        $currentHour = date('G');
        $currentMinutes = date('i');
        if ($currentHour >= 0 && $currentHour <= 1 && $currentMinutes >= 0 && $currentMinutes < 15) {
            echo "Now is: " . $currentHour . " Hour " . $currentMinutes . " Minutes \r\n";
            die();
        }

    }
}