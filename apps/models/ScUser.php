<?php

namespace Score\Models;

use Phalcon\Db\RawValue;

class ScUser extends \Phalcon\Mvc\Model
{
    const TYPE_SMS = 'sms';
    const TYPE_CALL = 'call';
    const TOKEN_LENGTH = 6;

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=10, nullable=false)
     */
    protected $user_id;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    protected $user_name;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    protected $user_email;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    protected $user_password;
    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    protected $user_tel;
    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    protected $user_country_code;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    protected $user_address;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    protected $user_reset_token;

    /**
     *
     * @var string
     * @Column(type="integer", nullable=false)
     */
    protected $user_token_time;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    protected $user_website;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    protected $user_address_country_code;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    protected $user_skype;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    protected $user_telapi_country_code;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    protected $user_telapi_international_format;


    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    protected $user_role_id;

    /**
     *
     * @var string
     */
    protected $user_is_subscribe;

    /**
     *
     * @var string
     */
    protected $user_unsubscribe_token;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $user_insert_time;

    /**
     *
     * @var string
     */
    protected $user_active;

    /**
     *
     * @var integer
     */
    protected $user_payment_fails;

    /**
     * Method to set the value of field user_id
     *
     * @param integer $user_id
     * @return $this
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;

        return $this;
    }

    /**
     * Method to set the value of field user_name
     *
     * @param string $user_name
     * @return $this
     */
    public function setUserName($user_name)
    {
        $this->user_name = $user_name;

        return $this;
    }

    /**
     * Method to set the value of field user_email
     *
     * @param string $user_email
     * @return $this
     */
    public function setUserEmail($user_email)
    {
        $this->user_email = $user_email;

        return $this;
    }

    /**
     * Method to set the value of field user_password
     *
     * @param string $user_password
     * @return $this
     */
    public function setUserPassword($user_password)
    {
        $this->user_password = $user_password;

        return $this;
    }

    /**
     * Method to set the value of field user_tel
     *
     * @param string $user_tel
     * @return $this
     */
    public function setUserTel($user_tel)
    {
        $this->user_tel = $user_tel;

        return $this;
    }

    /**
     * Method to set the value of field user_country_code
     *
     * @param string $user_country_code
     * @return $this
     */
    public function setUserCountryCode($user_country_code)
    {
        $this->user_country_code = $user_country_code;

        return $this;
    }

    /**
     * Method to set the value of field user_address_country_code
     *
     * @param string $user_address_country_code
     * @return $this
     */
    public function setUserAddressCountryCode($user_address_country_code)
    {
        $this->user_address_country_code = $user_address_country_code;

        return $this;
    }

    /**
     * Method to set the value of field user_address
     *
     * @param string $user_address
     * @return $this
     */
    public function setUserAddress($user_address)
    {
        $this->user_address = $user_address;

        return $this;
    }
    /**
     * Method to set the value of field user_reset_token
     *
     * @param string $user_reset_token
     * @return $this
     */
    public function setUserResetToken($user_reset_token)
    {
        $this->user_reset_token = $user_reset_token;

        return $this;
    }

    /**
     * Method to set the value of field user_token_time
     *
     * @param integer $user_token_time
     * @return $this
     */
    public function setUserTokenTime($user_token_time)
    {
        $this->user_token_time = $user_token_time;

        return $this;
    }

    /**
     * Method to set the value of field user_website
     *
     * @param string $user_website
     * @return $this
     */
    public function setUserWebsite($user_website)
    {
        $this->user_website = $user_website;

        return $this;
    }

    /**
     * Method to set the value of field user_skype
     *
     * @param string $user_skype
     * @return $this
     */
    public function setUserSkype($user_skype)
    {
        $this->user_skype = $user_skype;

        return $this;
    }

    /**
     * Method to set the value of field user_telapi_country_code
     *
     * @param string $user_telapi_country_code
     * @return $this
     */
    public function setUserTelapiCountryCode($user_telapi_country_code)
    {
        $this->user_telapi_country_code = $user_telapi_country_code;

        return $this;
    }

    /**
     * Method to set the value of field user_telapi_international_format
     *
     * @param string $user_telapi_international_format
     * @return $this
     */
    public function setUserTelapiInternationalFormat($user_telapi_international_format)
    {
        $this->user_telapi_international_format = $user_telapi_international_format;

        return $this;
    }

    /**
     * Method to set the value of field user_role_id
     *
     * @param integer $user_role_id
     * @return $this
     */
    public function setUserRoleId($user_role_id)
    {
        $this->user_role_id = $user_role_id;

        return $this;
    }

    /**
     * Method to set the value of field user_is_subscribe
     *
     * @param string $user_is_subscribe
     * @return $this
     */
    public function setUserIsSubscribe($user_is_subscribe)
    {
        $this->user_is_subscribe = $user_is_subscribe;

        return $this;
    }

    /**
     * Method to set the value of field user_unsubscribe_token
     *
     * @param string $user_unsubscribe_token
     * @return $this
     */
    public function setUserUnSubscribeToken($user_unsubscribe_token)
    {
        $this->user_unsubscribe_token = $user_unsubscribe_token;

        return $this;
    }

    /**
     * Method to set the value of field user_insert_time
     *
     * @param integer $user_insert_time
     * @return $this
     */
    public function setUserInsertTime($user_insert_time)
    {
        $this->user_insert_time = $user_insert_time;

        return $this;
    }

    /**
     * Method to set the value of field user_active
     *
     * @param string $user_active
     * @return $this
     */
    public function setUserActive($user_active)
    {
        $this->user_active = $user_active;

        return $this;
    }

    /**
     * Method to set the value of field user_payment_fails
     *
     * @param integer $user_payment_fails
     * @return $this
     */
    public function setUserPaymentFails($user_payment_fails)
    {
        $this->user_payment_fails = $user_payment_fails;

        return $this;
    }

    /**
     * Returns the value of field user_id
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Returns the value of field user_name
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->user_name;
    }

    /**
     * Returns the value of field user_email
     *
     * @return string
     */
    public function getUserEmail()
    {
        return $this->user_email;
    }

    /**
     * Returns the value of field user_password
     *
     * @return string
     */
    public function getUserPassword()
    {
        return $this->user_password;
    }

    /**
     * Returns the value of field user_tel
     *
     * @return string
     */
    public function getUserTel()
    {
        return $this->user_tel;
    }

    /**
     * Returns the value of field user_country_code
     *
     * @return string
     */
    public function getUserCountryCode()
    {
        return $this->user_country_code;
    }

    /**
     * Returns the value of field user_address
     *
     * @return string
     */
    public function getUserAddress()
    {
        return $this->user_address;
    }

    /**
     * Returns the value of field user_reset_token
     *
     * @return string
     */
    public function getUserResetToken()
    {
        return $this->user_reset_token;
    }

    /**
     * Returns the value of field user_token_time
     *
     * @return integer
     */
    public function getUserTokenTime()
    {
        return $this->user_token_time;
    }

    /**
     * Returns the value of field user_website
     *
     * @return string
     */
    public function getUserWebsite()
    {
        return $this->user_website;
    }

    /**
     * Returns the value of field user_address_country_code
     *
     * @return string
     */
    public function getUserAddressCountryCode()
    {
        return $this->user_address_country_code;
    }

    /**
     * Returns the value of field user_skype
     *
     * @return string
     */
    public function getUserSkype()
    {
        return $this->user_skype;
    }

    /**
     * Returns the value of field user_telapi_country_code
     *
     * @return string
     */
    public function getUserTelapiCountryCode()
    {
        return $this->user_telapi_country_code;
    }

    /**
     * Returns the value of field user_telapi_international_format
     *
     * @return string
     */
    public function getUserTelapiInternationalFormat()
    {
        return $this->user_telapi_international_format;
    }

    /**
     * Returns the value of field user_role_id
     *
     * @return integer
     */
    public function getUserRoleId()
    {
        return $this->user_role_id;
    }

    /**
     * Returns the value of field user_is_subscribe
     *
     * @return string
     */
    public function getUserIsSubscribe()
    {
        return $this->user_is_subscribe;
    }

    /**
     * Returns the value of field user_unsubscribe_token
     *
     * @return string
     */
    public function getUserUnSubscribeToken()
    {
        return $this->user_unsubscribe_token;
    }

    /**
     * Returns the value of field user_insert_time
     *
     * @return integer
     */
    public function getUserInsertTime()
    {
        return $this->user_insert_time;
    }

    /**
     * Returns the value of field user_active
     *
     * @return string
     */
    public function getUserActive()
    {
        return $this->user_active;
    }

    /**
     * Returns the value of field user_payment_fails
     *
     * @return integer
     */
    public function getUserPaymentFails()
    {
        return $this->user_payment_fails;
    }

    /**
     * Initialize method for model.
     */
//    public function initialize()
//    {
//        $this->setSchema("Sc_com");
//    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'sc_user';
    }
    public function beforeValidation()
    {
        if (empty($this->user_address)) {
            $this->user_address = new RawValue('\'\'');
        }
        if (empty($this->user_tel)) {
            $this->user_tel = new RawValue('\'\'');
        }
        if (empty($this->user_country_code)) {
            $this->user_country_code = new RawValue('\'\'');
        }
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ScUser[]|BaseModel\ResultsetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ScUser|BaseModel
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function findFirstByEmail($email)
    {
        return ScUser::findFirst(array(
            'user_email = :user_email: AND user_active="Y"',
            'bind' => array('user_email' => $email)
        ));
    }
    public static function findFirstByRole($role_id){
        return ScUser::findFirst(array(
            'user_role_id = :user_role: ',
            'bind' => array('user_role' => $role_id)
        ));
    }
    public  static function findFirstById($id){
        return ScUser::findFirst(array(
            'user_id = :user_id: ',
            'bind' => array('user_id' => $id)
        ));
    }
}
