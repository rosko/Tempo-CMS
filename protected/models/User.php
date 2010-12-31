<?php

class User extends CActiveRecord
{
	const ICON = '/images/icons/fatcow/16x16/user.png';

    const ADMIN_LOGIN = 'admin';
    private static $_admin;

    public $password_repeat='';

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public function name($language=null)
    {
        return $this->login ? $this->login : Yii::t('cms', 'New user', array(), null, $language);
    }

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'users';
	}

	public function rules()
	{
		return array(
            array('login, password', 'required', 'on'=>'add'),
            array('login', 'unique'),
			array('email, name', 'required'),
			array('login, email', 'length', 'max'=>32),
            array('email', 'email'),
            array('password', 'compare', 'compareAttribute'=>'password_repeat'),
            array('password, password_repeat', 'safe'),
            array('login', 'unsafe', 'on'=>'edit'),
			array('name', 'length', 'max'=>64),
		);
	}

	public function attributeLabels()
	{
		return array(
			'id' => Yii::t('cms', 'ID'),
			'login' => Yii::t('cms', 'Username'),
			'password' => Yii::t('cms', 'Password'),
            'password_repeat' => Yii::t('cms', 'Repeat password'),
			'email' => Yii::t('cms', 'E-mail'),
			'name' => Yii::t('cms', 'Name'),
		);
	}

    public function scheme()
    {
        return array(
            'id' => 'pk',
            'login' => 'char(32)',
            'password' => 'char(64)',
            'email' => 'char(64)',
            'name' => 'char(64)',
        );
    }

    public function form()
    {
        return array(
            'elements'=>array(
                'login'=>array(
                    'type'=>'text',
                ),
                'name'=>array(
                    'type'=>'text',
                ),
                'email'=>array(
                    'type'=>'text',
                ),
                'password'=>array(
                    'type'=>'password',
                    'value'=>'',
                ),
                'password_repeat'=>array(
                    'type'=>'password',
                ),
            ),
        );
    }

    public function defaultAccess()
    {
        return array(
            'create'=>'superadmin',
            'read'=>'authenticated',
            'update'=>'superadmin',
            'delete'=>'superadmin',
        );
    }

    public function install()
    {
        $user = new self;
        $user->login = self::ADMIN_LOGIN;
        $user->name = Yii::app()->params['admin']['name'];
        $user->email = Yii::app()->params['admin']['email'];
        $user->password = self::hash(Yii::app()->params['admin']['password']);
        $user->save(false);
    }

    public static function hash($string)
	{
        return sha1(md5($string) . Yii::app()->params['hashSalt']);
	}

    public function beforeSave()
    {
        if (!$this->password) {
            unset($this->password);
        } else {
            $this->password = self::hash($this->password);
        }
        return parent::beforeSave();
    }

    public function beforeDelete()
    {
        if ($this->id == 1 || $this->login == self::ADMIN_LOGIN) {
            return false;
        }
        return parent::beforeDelete();
    }

    public static function getByLogin($login)
    {
        return self::$_admin = self::model()->find('`login` = :login', array(':login'=>$login));
    }

    public static function getAdmin()
    {
		if(self::$_admin===null)
            self::$_admin = self::getByLogin(self::ADMIN_LOGIN);
        return self::$_admin;
    }
}