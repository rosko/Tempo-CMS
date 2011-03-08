<?php

class RememberForm extends CFormModel
{
	public $username;

	private $_identity;

	public function rules()
	{
		return array(
			array('username', 'required'),
            array('username', 'ManyExistValidator', 'attributeName'=>array('login','email'), 'className'=>'User', 'skipOnError'=>true),
            array('username', 'match', 'not'=>true, 'pattern'=>'/'.User::ADMIN_LOGIN.'/'),
		);
	}

	public function attributeLabels()
	{
		return array(
			'username' => Yii::t('cms', 'Username or e-mail'),
		);
	}

	public static function form()
	{
		return array(
			'elements'=>array(
				'username'=>array(
					'type'=>'text',
				),
			),
		);
	}

}
