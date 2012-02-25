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
		return Yii::app()->db->tablePrefix . 'units_login';
	}

	public function rules()
	{
		return array(
            array('unit_id', 'required', 'on'=>'edit'),
			array('unit_id', 'numerical', 'integerOnly'=>true),
		);
	}

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
//			'unit_id' => 'Unit',

		);
	}

    public function scheme()
    {
        return array(
            'unit_id' => 'integer unsigned',
        );
    }

    public function templateVars()
    {
        return array(
            '{$formButtons}' => Yii::t('UnitLogin.main', 'LoginForm buttons'),
        );
    }

}
