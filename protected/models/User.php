<?php

class User extends CActiveRecord
{
    const ACCESS_ADMINISTRATOR = 'administrator';
    const ACCESS_EDITOR = 'editor';
    const ACCESS_USER = 'user';

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'users';
	}

	public function rules()
	{
		return array(
			array('login, password, email, name, access', 'required'),
			array('login, email', 'length', 'max'=>32),
			array('name', 'length', 'max'=>64),
			array('access', 'length', 'max'=>13),
		);
	}

	public function attributeLabels()
	{
		return array(
			'id' => Yii::t('cms', 'ID'),
			'login' => Yii::t('cms', 'Username'),
			'password' => Yii::t('cms', 'Password'),
			'email' => Yii::t('cms', 'E-mail'),
			'name' => Yii::t('cms', 'Name'),
			'access' => Yii::t('cms', 'Access'),
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
            'access' => 'string',
        );
    }

    public function install()
    {
        $user = new self;
        $user->login = 'admin';
        $user->name = Yii::app()->params['admin']['name'];
        $user->email = Yii::app()->params['admin']['email'];
        $user->password = self::hash(Yii::app()->params['admin']['password']);
        $user->access = self::ACCESS_ADMINISTRATOR;
        $user->save(false);
    }

    public static function hash($string)
	{
        return sha1(md5($string) . Yii::app()->params['hashSalt']);
	}
}