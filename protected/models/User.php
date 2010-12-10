<?php

class User extends CActiveRecord
{
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
}