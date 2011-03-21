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
		$user=User::model()->find(
            '(`login`= :username OR `email`= :username) AND (`active` = 1 OR `login` = :adminlogin)',
            array(
                'username'=>$this->username,
                'adminlogin'=>User::ADMIN_LOGIN,
            )
        );
		if($user===null)
			$this->errorCode=self::ERROR_USERNAME_INVALID;
		else if(User::hash($this->password)!==$user->password)
			$this->errorCode=self::ERROR_PASSWORD_INVALID;
		else {
            if ($user->askfill)
                $this->setState('askfill', 'first');
            $this->_id = $user->id;
            $this->username = $user->login ? $user->login : $user->email;
			$this->errorCode=self::ERROR_NONE;
		}
		return !$this->errorCode;
	}

}