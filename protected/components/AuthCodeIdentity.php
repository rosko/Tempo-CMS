<?php

class AuthCodeIdentity extends CUserIdentity
{
    private $_id;
    public $authcode;

    public function getId()
    {
        return $this->_id;
    }

	public function __construct($authcode)
	{
		$this->authcode=$authcode;
	}

	public function authenticate()
	{
        $user = User::model()->find('`authcode`=:authcode', array('authcode'=>$this->authcode));
		if($user===null)
			$this->errorCode=self::ERROR_USERNAME_INVALID;
		else {
            $this->_id = $user->id;
            $this->username = $user->login ? $user->login : $user->email;
			$this->errorCode=self::ERROR_NONE;
		}
		return !$this->errorCode;
	}
    
}