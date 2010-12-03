<?php

class User extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'users';
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

}