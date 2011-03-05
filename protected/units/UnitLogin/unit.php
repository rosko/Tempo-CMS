<?php

class UnitLogin extends Content
{
	const ICON = '/images/icons/fatcow/16x16/user.png';
    const HIDDEN = true;

    public function name($language=null)
    {
        return Yii::t('UnitLogin.unit', 'Login Form', array(), null, $language);
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
			array('unit_id', 'required'),
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
            'id' => 'pk',
            'unit_id' => 'integer unsigned',
        );
    }

    public function templateVars()
    {
        return array(
            '{$formButtons}' => Yii::t('UnitLogin.unit', 'LoginForm buttons'),
        );
    }

    public function prepare($params)
    {
        $params = parent::prepare($params);
        $params['formButtons'] = array(
            'login'=>array(
                'type'=>'submit',
                'label'=>Yii::t('UnitLogin.unit', 'Login'),
                'title'=>Yii::t('UnitLogin.unit', 'Login'),
            ),
        );

        if(isset($_POST['logout'])) {
            Yii::app()->user->logout();
            Yii::app()->controller->refresh();
        }
        $model=new LoginForm;
		if(isset($_POST['LoginForm']))
		{
			$model->attributes=$_POST['LoginForm'];
			if($model->validate() && $model->login()) {
               Yii::app()->controller->refresh();
			}
		}
        return $params;
    }

    public function ajax($vars)
    {
        $model=new LoginForm;
		if(isset($_REQUEST['ajax-validate']))
		{
            echo CActiveForm::validate($model);
			Yii::app()->end();
		}
		if(isset($_POST['LoginForm']))
		{
			$model->attributes=$_POST['LoginForm'];
			if($model->validate() && $model->login()) {
                echo '1';
			}
		}
    }

}