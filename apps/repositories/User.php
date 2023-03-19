<?php

namespace Score\Repositories;

use Score\Models\ScUser;
use Score\Models\ScRole;
use Phalcon\Mvc\User\Component;

class User extends Component
{
    /**
     * @var ScUser $user
     * @var ScRole $role
     */
    public function initSession($user, $role)
    {
        if ($user) {
            $countryCodeUser = $user->getUserCountryCode();
            $countryName = '';
            $countryCode = '';
            $addressCountryCodeUser = $user->getUserAddressCountryCode();
            $addressCountryName = '';
            $addressCountryCode = '';
            $role_name = ($role) ? $role->getRoleName() : "user";
            $this->session->set('auth', array(
                'id' => $user->getUserId(),
                'name' => $user->getUserName(),
                'email' => $user->getUserEmail(),
                'role' => $role_name,
                'insertTime' => $user->getUserInsertTime(),
                'countryName' => $countryName,
                'countryCode' => $countryCode,
                'address' => $user->getUserAddress(),
                'addressCountryCodeUser' => $user->getUserAddressCountryCode(),
                'addressCountryCode' => $addressCountryCode,
                'addressCountryName' => $addressCountryName,
                'skype' => $user->getUserSkype(),
                'tel' => $user->getUserTel(),
                'tel_international_format' => $user->getUserTelapiInternationalFormat(),
            ));
        }
        return false;
    }
    public function redirectLogged($pre = "")
    {
        if ($this->session->has('preURL')) {
            $preURL = $this->session->get('preURL');
            $this->session->remove('preURL');
            if (strlen($preURL) > 1 && $preURL != "/") {
                $this->response->redirect($preURL);
                return;
            }
        }
        if ($pre == "")
            $this->response->redirect("my-account");
        else
            $this->response->redirect($pre);
    }
    public static function getByLimit($limit)
    {
        return ScUser::find(array(
            "order"      => "user_insert_time DESC",
            "limit"      => $limit,
        ));
    }
    public  static function getCount()
    {
        return ScUser::count();
    }
    public static function getFirstUserByUserId($user_id)
    {
        return ScUser::findFirst(array(
            'user_id = :user_id:',
            'bind' => array('user_id' => $user_id)
        ));
    }
    public static function findFirstByEmail($email)
    {
        return ScUser::findFirst(array(
            'user_email  = :user_email:',
            'bind' => array('user_email' => $email)
        ));
    }
    public function saveUser($newUser)
    {
        if ($newUser->save()) {
            $result['success'] = true;
            $result['id'] = $newUser->getUserId();
        } else {
            $message =  "We can't store your info now: \n";
            foreach ($newUser->getMessages() as $msg) {
                $message .= $msg . "\n";
            }
            $result['success'] = false;
            $result['message'] = $message;
        }
        return $result;
    }
    public static function findByEmail($userEmail)
    {
        return ScUser::findFirst(array(
            "user_email=:userEmail: AND user_active='Y'",
            "bind" => array("userEmail" => $userEmail)
        ));
    }

    public static function findByUnsubscribeToken($token)
    {
        return ScUser::findFirst(array(
            "user_unsubscribe_token=:token: AND user_active='Y'",
            "bind" => array("token" => $token)
        ));
    }
}
