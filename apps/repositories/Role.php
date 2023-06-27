<?php

namespace Score\Repositories;

use Mpdf\Tag\S;
use Score\Models\ScRole;
use Phalcon\Mvc\User\Component;

class Role extends Component
{

    // get by ID Role
    public static function getByID($role_id)
    {
        return ScRole::findFirst(array(
            'role_id = :role_id:',
            'bind' => array('role_id' => $role_id)
        ));
    }
    // get by Name Role
    public static function getByName($role_name, $role_id)
    {
        return ScRole::findFirst(array(
            'role_name = :role_name: AND role_id != :role_id:',
            'bind' => array(
                'role_id' => $role_id,
                'role_name' => $role_name
            )
        ));
    }
    public static function getFirstByName($role_name)
    {
        return ScRole::findFirst(array(
            'role_name = :role_name:',
            'bind' => array('role_name' => $role_name)
        ));
    }
    // get Function Role
    public function getFunctionRole($num_of_cols, $resources, $values, $prefix)
    {
        $row_count = 0;
        $bootstrap_col_width = 12 / $num_of_cols;
        $default_actions = Role::getActions();
        $result = "<div class='row'>";
        foreach ($resources as $key => $items) {
            $name = str_replace($prefix, '', $key);
            $result_control_head = "";
            $result_control_end = "";
            $default_control = array();
            if (isset($default_actions[$key]))
                $default_control = $default_actions[$key];
            if (count($items) > count($default_control)) {
                $result_control_head = "<div class='role_block col-md-" . $bootstrap_col_width . "'>
                <div class='well'><h2 class='text-danger'>" . ucfirst($name) . "</h2>";
                $result_control_end = "</div></div>";
                $row_count++;
            }
            $result .= $result_control_head;

            foreach ($items as $item) {
                if (!in_array($item, $default_control)) {
                    $seletced = (isset($values[$key]) && in_array($item, $values[$key])) ? 'checked' : '';
                    $result .=
                        "<label class='container_checkbox'> " . ucfirst($item) . "
                        <input type='checkbox' class='form-control check' name='" . $key . "[]' id='" . $key . $item . "' " . $seletced . " value='" . $item . "' />
                        <span class='checkmark_checkbox'></span>
                    </label><div class='clearfix'></div>";
                }
            }
            $result .= $result_control_end;
            if ($row_count % $num_of_cols == 0) $result .= "</div><div class='row'>";
        }
        $result .= "</div>";
        return $result;
    }
    public static function checkMenu($role_action, $role_default)
    {
        $result = false;
        $actions = self::getActionsShow();
        foreach ($role_default as $controller) {
            if (isset($role_action[$controller])) {
                if (in_array('index', $role_action[$controller])) {
                    $result = true;
                    break;
                }
                if (!empty($actions[$controller]['actions'])) {
                    foreach ($actions[$controller]['actions'] as $action) {
                        if (in_array($action, $role_action[$controller])) {
                            $result = true;
                            break;
                        }
                    }
                }
            }
        }
        return $result;
    }
    public static function checkMenuSub($role_action, $role_current)
    {
        $result = false;
        $pre = "backend";

        if (in_array($pre . $role_current, $role_action)) {
            $result = true;
        }
        return $result;
    }
    public static function checkiMenuItem($role_action, $role_current)
    {
        $result = false;
        $pre = "backend";
        if (isset($role_action[$pre . $role_current]) && in_array('index', $role_action[$pre . $role_current])) {
            $result = true;
        }
        return $result;
    }
    public static  function getAllActive()
    {
        return ScRole::find("role_active ='Y'");
    }
    public static function getComboBox($role_id)
    {
        $data =  ScRole::find(array("order" => "role_order"));

        $output = "";
        foreach ($data as  $value) {
            $selected = "";
            if ($value->getRoleId() == $role_id) {
                $selected = "selected = 'selected'";
            }
            $output .= "<option " . $selected . " value=" . $value->getRoleId() . ">" . $value->getRoleName() . "</option>";
        }
        return $output;
    }
    public static function geScameRole($role_id)
    {
        $role = ScRole::findFirst(array(
            'role_id = :role_id:',
            'bind' => array('role_id' => $role_id)
        ));
        return ($role) ? $role->getRoleName() : '';
    }
    public static function getFirstLoginByName($namerole)
    {
        return ScRole::findFirst(array(
            'role_name = :role_name: AND role_active="Y"',
            'bind' => array('role_name' => $namerole)
        ));
    }

    /**
     * @param integer $id
     * @return ScRole
     */
    public static function getFirstLoginById($id)
    {
        return ScRole::findFirst(array(
            'role_id = :role_id: AND role_active="Y"',
            'bind' => array('role_id' => $id)
        ));
    }
    public static function getFirstById($id)
    {
        return ScRole::findFirst(array(
            'role_id = :role_id:',
            'bind' => array('role_id' => $id)
        ));
    }
    public static function getGuestUser()
    {
        return array('guest', 'user');
    }
    public static function getActions()
    {
        return array(
            'backendnotfound' => array('index', 'notfound'),
            'backendindex' => array('index', 'accessdenied'),
            'backendlogin' => array('index', 'logout'),
            'backendairports' => array('getcountrybyarea', 'getairportcodebycountry', 'getcitybycountry'),
            'backendcron' => array('*'),
            'backendlocation' => array('getlocationtobylocationfrom', 'getlangbycode'),
            'backendcurrencycountry' => array('getcurrencybycode'),
            'backendcopydata' => array('translatedata', 'copyData', 'insertVN', 'inserttranslate', 'copydatatableseo'),
            'backendarticle' => array('updatecontent'),
            'backendfaq' => array('updatecontent'),
            'backendcountry' => array('getcountrybyarea'),
            'backendtopflight' => array('getsuggestions'),
            'backendticket' => array('getidnameemailuser', 'getsuggestions', 'getinfoticket'),
            'backendvoucherhotel' => array('voucherInfos', 'validate'),
            'backendtickeScew' => array('getidnameemailuser', 'getsuggestions', 'getinfo', 'uploadpdfs3'),
            'backendflightdestination' => array('updateimage', 'resizeimage'),
            'backendflightroute' => array('autofill'),
            'backendflighbooking' => array('deleteticket', 'updateBooking'),
            'backendlanguagetabletranslate' => array('getlanguage'),
            'backendzohocrm' => array(
                'listLeads',
                'insertLeads',
                'deleteLeads',
                'listContacts',
                'insertContacts',
                'deleteContacts',
                'listDeals',
                'insertDeals',
                'deleteDeals'
            ),
        );
    }
    public static function getControllers()
    {
        return array(
            'match' =>  [
                'icon' => 'fa-list',
                'controllers' => [
                    'Tournament' => 'backendtournament',
                    'Team' => 'backendteam',
                    'Match' => 'backendmatch',
                ]
            ],
            'setting' => [
                'icon' => "fa-cogs",
                'controllers' => array(
                    'Language' => 'backendlanguage',
                    'Role' => 'backendrole',
                    'Page' => 'backendpage',
                    'Config' => 'backendconfigcontent',
                )
            ],
            'structure' => [
                'icon' => 'fa-sitemap',
                'controllers' => [
                    'Country' => 'backendcountry',
                    'Area' => 'backendarea',
                ]
            ],
            'support' => [
                'icon' => 'fa-quote-left',
                'controllers' => [
                    'Cloud Upload' => 'backendcloudupload',
                    'Subscribe' => 'backendsubscribe',
                    'User' => 'backenduser',
                    'Delete Cache' => 'backenddeletecachetool',
                ]
            ],
            'content' => [
                'icon' => "fa-sliders",
                'controllers' => [
                    'Communication Channel' => 'backendcommunicationchannel',
                    'Type' => 'backendtype',
                    'Article' => 'backendarticle',
                ]
            ]


        );
    }
    public static function getActionsShow()
    {
        return [
            // 'backendzohocrm' => [
            //     'icon' => "fa-user-plus",
            //     'actions' => [
            //         'listLeads',
            //         'listContacts',
            //         'listDeals'
            //     ]
            // ],
            // 'backendcopydata' => [
            //     'icon' => "fa-user-plus",
            //     'actions' => [
            //         'copydatabylocationlang',
            //         'copydatabylangandlocation',
            //     ]
            // ]
        ];
    }
}
