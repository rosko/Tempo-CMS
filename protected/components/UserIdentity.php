<?php

class UserIdentity extends CUserIdentity
{
    private $_id;
    
    public function getId()
    {
        return $this->_id;
    }

	public function authenticate()
	{
		$user=User::model()->find('`login`= :username OR `email`= :username',array('username'=>$this->username));
		if($user===null)
			$this->errorCode=self::ERROR_USERNAME_INVALID;
		else if(User::hash($this->password)!==$user->password)
			$this->errorCode=self::ERROR_PASSWORD_INVALID;
		else {
            $this->_id = $user->id;
            $this->username = $user->login;
			$this->errorCode=self::ERROR_NONE;
		}
		return !$this->errorCode;
	}

}