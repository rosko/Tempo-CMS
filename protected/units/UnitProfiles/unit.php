<?php

class UnitProfiles extends Content
{
	const ICON = '/images/icons/fatcow/16x16/user.png';
    const HIDDEN = true;

    public function name($language=null)
    {
        return Yii::t('UnitProfiles.unit', 'User profiles', array(), null, $language);
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
			array('unit_id', 'required'),
			array('unit_id, per_page', 'numerical', 'integerOnly'=>true),
            array('table_fields, displayed_fields, profile_fields, profile_fields_req', 'type', 'type'=>'array'),
		));
	}

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
//			'unit_id' => 'Unit',
            'table_fields' => Yii::t('UnitProfiles.unit', 'Fields in users table'),
            'displayed_fields' => Yii::t('UnitProfiles.unit', 'Displayed fields in user profile'),
            'profile_fields' => Yii::t('UnitProfiles.unit', 'Editable fields in user profile'),
            'profile_fields_req' => Yii::t('UnitProfiles.unit', 'Required editable fields in user profile'),
            'per_page' => Yii::t('UnitProfiles.unit', 'Rows in table per page'),
		);
	}

    public function scheme()
    {
        return array(
            'id' => 'pk',
            'unit_id' => 'integer unsigned',
            'table_fields' => 'text', // поля отображаемые в таблице пользователей
            'displayed_fields' => 'text', // поля отображаемые при просмотре профиля пользователя
            'profile_fields' => 'text', // поля, которые пользователь может заполнить в своем профиле
            'profile_fields_req' => 'text', // поля, которые пользователь обязан заполнить в своем профиле
            'per_page' => 'integer unsigned', // количество профилей в таблице на одну страницу
        );
    }

    public function behaviors()
    {
        return array(
            'CSerializeBehavior' => array(
                'class' => 'application.behaviors.CSerializeBehavior',
                'serialAttributes' => array('table_fields', 'displayed_fields', 'profile_fields', 'profile_fields_req'),
            )
        );
    }

    public static function restrictedProfileFields()
    {
        return array('password', 'password_repeat', 'captcha', 'active', 'authcode', 'agreed', 'askfill', 'show_email', 'send_message');
    }

	public static function form()
	{
        $arr = User::form();
        $labels = User::attributeLabels();
        $fields_array = array();
        $all_fields_array = array();
        $restricted_fields = UnitProfiles::restrictedProfileFields();
        foreach (array_keys($arr['elements']) as $k) {
            if (!in_array($k, $restricted_fields))
                $fields_array[$k] = $labels[$k];
            $all_fields_array[$k] = $labels[$k];
        }
		return array(
			'elements'=>array(
                Form::tab(Yii::t('UnitProfiles.unit', 'Settings')),
                'table_fields'=>array(
                    'type'=>'listbox',
                    'multiple'=>true,
                    'size'=>7,
                    'items'=>$fields_array,
                ),
                'displayed_fields'=>array(
                    'type'=>'listbox',
                    'multiple'=>true,
                    'size'=>7,
                    'items'=>$fields_array,
                ),
/*                'profile_fields'=>array(
                    'type'=>'listbox',
                    'multiple'=>true,
                    'size'=>7,
                    'items'=>$all_fields_array,
                ),
                'profile_fields_req'=>array(
                    'type'=>'listbox',
                    'multiple'=>true,
                    'size'=>7,
                    'items'=>$all_fields_array,
                ),*/
				'per_page'=>array(
					'type'=>'Slider',
					'options'=>array(
						'min' => 0,
						'max' => 50,
                        'step' => 5,
					)
				),
                Yii::t('UnitProfiles.unit', 'If zero choosed, accordingly site\'s general settings'),
            )
        );

    }

    public function prepare($params)
    {
        $params = parent::prepare($params);

        return $params;
    }

}