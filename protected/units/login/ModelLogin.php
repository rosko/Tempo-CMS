<?php

class ModelLogin extends ContentModel
{
    public function icon()
    {
        return '/images/icons/fatcow/16x16/user.png';
    }
    
    public function modelName($language=null)
    {
        return Yii::t('UnitLogin.main', 'Login Form', array(), null, $language);
    }

    public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'widgets_login';
	}

	public function rules()
	{
		return array(
            array('widget_id', 'required', 'on'=>'edit'),
			array('widget_id', 'numerical', 'integerOnly'=>true),
		);
	}

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
//			'widget_id' => 'Widget',

		);
	}

    public function scheme()
    {
        return array(
            'widget_id' => 'integer unsigned',
        );
    }

    public function templateVars()
    {
        return array(
            '{$formButtons}' => Yii::t('UnitLogin.main', 'LoginForm buttons'),
        );
    }

}
