<?php

Yii::$classMap['UnitProfilesWidget'] = Yii::getPathOfAlias('application.units.UnitProfiles.widget').'.php';

class UnitProfiles extends ContentModel
{
    public function icon()
    {
        return '/images/icons/fatcow/16x16/user.png';
    }
    
    public function hidden()
    {
        return true;
    }

    public function unitName($language=null)
    {
        return Yii::t('UnitProfiles.main', 'User profiles', array(), null, $language);
    }

    public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'units_profiles';
	}

	public function rules()
	{
		return $this->localizedRules(array(
            array('unit_id', 'required', 'on'=>'edit'),
			array('unit_id, per_page', 'numerical', 'integerOnly'=>true),
            array('table_fields, displayed_fields', 'type', 'type'=>'array'),
            array('feedback_form', 'safe'),
		));
	}

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
//			'unit_id' => 'Unit',
            'table_fields' => Yii::t('UnitProfiles.main', 'Fields in users table'),
            'displayed_fields' => Yii::t('UnitProfiles.main', 'Displayed fields in user profile'),
            'per_page' => Yii::t('UnitProfiles.main', 'Rows in table per page'),
            'feedback_form' => Yii::t('UnitProfiles.main', 'Feedback form'),
		);
	}

    public function scheme()
    {
        return array(
            'id' => 'pk',
            'unit_id' => 'integer unsigned',
            'create' => 'datetime',
            'modify' => 'datetime',
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

    public function urlParam($method)
    {
        return 'profile_'.$method;
    }

    public function urlParams()
    {
        $list = array(
            'view', 'do', 'page', 'sort'
        );
        $ret = array();
        foreach ($list as $param) {
            $ret[] = self::urlParam($param);
        }
        return $ret;
    }

    public function cacheVaryBy()
    {
        return array(
            'isGuest'=>Yii::app()->user->isGuest,
        );
    }

    public function cacheRequestTypes()
    {
        return array('GET');
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

