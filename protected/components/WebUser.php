<?php
class WebUser extends CWebUser
{
    private $_data=null;
        
    public function getData()
    {
        if (!$this->_data && !$this->isGuest) {
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
        if (!$this->isGuest && $this->data) {
            return $this->data->roles;
        } else {
            return $this->getGuestRoles();
         }
    }

    public function hasRole($role)
    {
        return in_array($role, $this->roles);
    }

    public function checkAccess($operation,$params=array(),$allowCaching=true)
    {
        if($allowCaching && $params===array() && isset($this->_access[$operation]))
            return $this->_access[$operation];

        if ($this->isGuest) {
            $user = new User;
            $user->roles = $this->getGuestRoles();
        } else {
            $user = $this->data;
        }

        $access = $user->may($operation, isset($params['object']) ? $params['object'] : array());

        if($allowCaching && $params===array())
            $this->_access[$operation]=$access;

        return $access;
    }

}