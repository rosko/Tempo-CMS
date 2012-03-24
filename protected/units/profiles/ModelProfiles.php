<?php

class ModelProfiles extends ContentModel
{
    public function icon()
    {
        return '/images/icons/fatcow/16x16/user.png';
    }
    
    public function modelName($language=null)
    {
        return Yii::t('UnitProfiles.main', 'User profiles', array(), null, $language);
    }

    public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'widgets_profiles';
	}

	public function rules()
	{
		return $this->localizedRules(array(
            array('widget_id', 'required', 'on'=>'edit'),
			array('widget_id, per_page', 'numerical', 'integerOnly'=>true),
            array('table_fields, displayed_fields', 'type', 'type'=>'array'),
            array('feedback_form', 'safe'),
		));
	}

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
//			'widget_id' => 'Widget',
            'table_fields' => Yii::t('UnitProfiles.main', 'Fields in users table'),
            'displayed_fields' => Yii::t('UnitProfiles.main', 'Displayed fields in user profile'),
            'per_page' => Yii::t('UnitProfiles.main', 'Rows in table per page'),
            'feedback_form' => Yii::t('UnitProfiles.main', 'Feedback form'),
		);
	}

    public function scheme()
    {
        return array(
            'widget_id' => 'integer unsigned',
            'table_fields' => 'text', // поля отображаемые в таблице пользователей
            'displayed_fields' => 'text', // поля отображаемые при просмотре профиля пользователя
            'per_page' => 'integer unsigned', // количество профилей в таблице на одну страницу
            'feedback_form' => 'text',
        );
    }

    public function behaviors()
    {
        return array(
            'CSerializeBehavior' => array(
                'class' => 'application.behaviors.CSerializeBehavior',
                'serialAttributes' => array('table_fields', 'displayed_fields', 'feedback_form'),
            )
        );
    }

    public function  cacheDependencies() {
        return array(
            array(
                'class'=>'system.caching.dependencies.CDbCacheDependency',
                'sql'=>'SELECT CONCAT(MAX(`create`),MAX(`modify`)) FROM `' . User::tableName() . '`',
            ),
        );
    }

	public static function form()
	{
        $model = new User;
        $viewFields = $model->proposedFields('view', true);

		return array(
			'elements'=>array(
                Form::tab(Yii::t('UnitProfiles.main', 'Settings')),
                'table_fields'=>array(
                    'type'=>'listbox',
                    'multiple'=>true,
                    'size'=>7,
                    'items'=>$viewFields,
                ),
                'displayed_fields'=>array(
                    'type'=>'listbox',
                    'multiple'=>true,
                    'size'=>7,
                    'items'=>$viewFields,
                ),
				'per_page'=>array(
					'type'=>'Slider',
                    'hint'=>Yii::t('UnitProfiles.main', 'If zero choosed, accordingly site\'s general settings'),
					'options'=>array(
						'min' => 0,
						'max' => 50,
                        'step' => 5,
					)
				),
                Form::tab(Yii::t('UnitProfiles.main', 'Feedback form')),
                'feedback_form'=>array(
                    'type'=>'FieldSet',
                ),
                
            )
        );

    }

}

