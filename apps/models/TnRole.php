<?php

namespace Score\Models;

use Phalcon\Db\RawValue;

class ScRole extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=10, nullable=false)
     */
    protected $role_id;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    protected $role_name;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $role_active;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    protected $role_order;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $role_function;

    /**
     * Method to set the value of field role_id
     *
     * @param integer $role_id
     * @return $this
     */
    public function setRoleId($role_id)
    {
        $this->role_id = $role_id;

        return $this;
    }

    /**
     * Method to set the value of field role_name
     *
     * @param string $role_name
     * @return $this
     */
    public function setRoleName($role_name)
    {
        $this->role_name = $role_name;

        return $this;
    }

    /**
     * Method to set the value of field role_active
     *
     * @param string $role_active
     * @return $this
     */
    public function setRoleActive($role_active)
    {
        $this->role_active = $role_active;

        return $this;
    }

    /**
     * Method to set the value of field role_order
     *
     * @param integer $role_order
     * @return $this
     */
    public function setRoleOrder($role_order)
    {
        $this->role_order = $role_order;

        return $this;
    }

    /**
     * Method to set the value of field role_function
     *
     * @param string $role_function
     * @return $this
     */
    public function setRoleFunction($role_function)
    {
        $this->role_function = $role_function;

        return $this;
    }

    /**
     * Returns the value of field role_id
     *
     * @return integer
     */
    public function getRoleId()
    {
        return $this->role_id;
    }

    /**
     * Returns the value of field role_name
     *
     * @return string
     */
    public function getRoleName()
    {
        return $this->role_name;
    }

    /**
     * Returns the value of field role_active
     *
     * @return string
     */
    public function getRoleActive()
    {
        return $this->role_active;
    }

    /**
     * Returns the value of field role_order
     *
     * @return integer
     */
    public function getRoleOrder()
    {
        return $this->role_order;
    }

    /**
     * Returns the value of field role_function
     *
     * @return string
     */
    public function getRoleFunction()
    {
        return $this->role_function;
    }

    /**
     * Initialize method for model.
     */
//    public function initialize()
//    {
//        $this->setSchema("bincgcom");
//    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'sc_role';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ScRole[]|ScRole
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ScRole
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }
    public function beforeValidation()
    {
        if (empty($this->role_name)) {
            $this->role_name = new RawValue('\'\'');
        }
        if (empty($this->role_function)) {
            $this->role_function = new RawValue('\'\'');
        }

    }
}
