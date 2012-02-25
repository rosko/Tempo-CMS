<?php
class WebUser extends CWebUser
{
    private $_data=null;
        
    public function getData()
    {
        if (!$this->_data && !Yii::app()->user->isGuest) {
           $this->_data = User::model()->findByPk(Yii::app()->user->id);
        }
        return $this->_data;
    }
    
    public function setData($value)
    {
        $this->_data = $value;
    }
    
}