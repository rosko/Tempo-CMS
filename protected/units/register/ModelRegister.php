<?php

class ModelRegister extends ContentModel
{
    public function icon()
    {
        return '/images/icons/fatcow/16x16/user.png';
    }
    
    public function modelName($language=null)
    {
        return Yii::t('UnitRegister.main', 'Registration and profile form', array(), null, $language);
    }

    public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'units_register';
	}

	public function rules()
	{
		return $this->localizedRules(array(
            array('unit_id', 'required', 'on'=>'edit'),
			array('unit_id', 'numerical', 'integerOnly'=>true),
            array('is_emailauth_req, is_invite_req, notify_admin, notify_user', 'boolean'),
            array('fields, fields_req, profile_fields, profile_fields_req', 'type', 'type'=>'array'),
            array('invites, agreement, text', 'safe'),
		));
	}

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
//			'unit_id' => 'Unit',
            'fields' => Yii::t('UnitRegister.main', 'Form fields'),
            'fields_req' => Yii::t('UnitRegister.main', 'Required fields'),
            'profile_fields' => Yii::t('UnitRegister.main', 'Editable fields in user profile'),
            'profile_fields_req' => Yii::t('UnitRegister.main', 'Required editable fields in user profile'),
            'is_emailauth_req' => Yii::t('UnitRegister.main', 'Is e-mail authorization needed?'),
            'is_invite_req' => Yii::t('UnitRegister.main', 'Is invite required?'),
            'invites' => Yii::t('UnitRegister.main', 'Invites'),
            'notify_admin' => Yii::t('UnitRegister.main', 'Notify administrator about new user'),
            'notify_user' => Yii::t('UnitRegister.main', 'Notify user after successfull registration'),
            'agreement' => Yii::t('UnitRegister.main', 'User agreement'),
            'text' => Yii::t('UnitRegister.main', 'Text'),
		);
	}

    public function scheme()
    {
        return array(
            'unit_id' => 'integer unsigned',
            'fields' => 'text',
            'fields_req' => 'text',
            'profile_fields' => 'text', // поля, которые пользователь может заполнить в своем профиле
            'profile_fields_req' => 'text', // поля, которые пользователь обязан заполнить в своем профиле
            'is_emailauth_req'=>'boolean',
            'notify_admin'=>'boolean',
            'notify_user'=>'boolean',
            'is_invite_req'=>'boolean',
            'invites'=>'text',
            'agreement'=>'text',
            'text'=>'text',
        );
    }

    public function i18n()
    {
        return array('agreement', 'text');
    }

    public function behaviors()
    {
        return array(
            'CSerializeBehavior' => array(
                'class' => 'application.behaviors.CSerializeBehavior',
                'serialAttributes' => array('fields', 'fields_req', 'profile_fields', 'profile_fields_req'),
            )
        );
    }

    public static function defaultRegFields()
    {
        return array('email');
    }

    public static function restrictedRegFields()
    {
        return array('active', 'askfill', 'show_email', 'send_message');
    }

	public static function form()
	{
        $model = new User;
        $registerFields = $model->proposedFields('register', true);
        $updateFields = $model->proposedFields('update', true);
        
		return array(
			'elements'=>array(
                Form::tab(Yii::t('UnitRegister.main', 'Settings')),
                'fields'=>array(
                    'type'=>'listbox',
                    'multiple'=>true,
                    'size'=>7,
                    'items'=>$registerFields,
                ),
                'fields_req'=>array(
                    'type'=>'listbox',
                    'multiple'=>true,
                    'size'=>7,
                    'items'=>$registerFields,
                ),
                'is_emailauth_req'=>array(
                    'type'=>'checkbox',
                ),
                'notify_admin'=>array(
                    'type'=>'checkbox',
                ),
                'notify_user'=>array(
                    'type'=>'checkbox',
                ),
                Form::tab(Yii::t('UnitRegister.main', 'Text')),
                'text'=>array(
					'type'=>'TextEditor',
                    'kind'=>'fck',
                ),
                Form::tab(Yii::t('UnitRegister.main', 'User agreement')),
                'agreement'=>array(
					'type'=>'TextEditor',
                    'kind'=>'fck',
                ),
                Form::tab(Yii::t('UnitRegister.main', 'Editing profile')),
                'profile_fields'=>array(
                    'type'=>'listbox',
                    'multiple'=>true,
                    'size'=>7,
                    'items'=>$updateFields,
                ),
                'profile_fields_req'=>array(
                    'type'=>'listbox',
                    'multiple'=>true,
                    'size'=>7,
                    'items'=>$updateFields,
                ),
// TODO: Поддержку инвайтов сделаем позже
/*                'is_invite_req'=>array(
                    'type'=>'checkbox',
                ),
                'invites'=>array(
                    'type'=>'textarea',
                    'rows'=>'10',
                    'cols'=>'50',
                )*/
            )
        );
        
    }

    public function templateVars()
    {
        return array(
            // TODO
        );
    }

}
