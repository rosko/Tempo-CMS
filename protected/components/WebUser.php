<?php
class WebUser extends CWebUser
{
    private $_data=null;
    private $_access=array();

    public function getData()
    {
        if (!$this->isGuest) {
            if (!$this->_data) {
                try
                {
                    $this->_data = User::model()->with('_roles')->findByPk($this->id);
                }
                catch(Exception $e)
                {
                    if (Yii::app()->db->active) {
                        Yii::app()->installer->installAll();
                        $this->_data = User::model()->with('_roles')->findByPk($this->id);
                    } else
                        echo Yii::t('cms', 'Error! Check configuration file "protected/config/config.php", is database setting correct. Or delete configuration file for installing system.');
                }
            }
        } else {
            $this->_data = new User;
            $this->_data->roles = $this->getGuestRoles();
        }
        return $this->_data;
    }

    public function setData($value)
    {
        $this->_data = $value;
    }

    protected function getGuestRoles()
    {
        return array(Role::ANYBODY, Role::GUEST);
    }

    public function getRoles()
    {
        return $this->data->roles;
    }

    public function hasRole($role)
    {
        return in_array($role, $this->roles);
    }

    public function checkAccess($operation,$params=array(),$allowCaching=true)
    {
        if($allowCaching && isset($this->_access[$operation]))
            return $this->_access[$operation];

        if ($params===array()) {
            return $this->data->checkFullAccess() || $this->hasRole($operation);
        }

        $access = $this->data->may($operation, isset($params['object']) ? $params['object'] : array());

        if($allowCaching && $params===array())
            $this->_access[$operation]=$access;

        return $access;
    }

}