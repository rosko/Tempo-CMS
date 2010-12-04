<?php

class UnitLangswitcher extends Content
{
	const ICON = '/images/icons/fatcow/16x16/style_go.png';
    const HIDDEN = false;

    public function name()
    {
        return Yii::t('UnitLangswitcher.unit', 'Language switcher');
    }
    
    public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'units_langswitcher';
	}

	public function rules()
	{
		return array(
			array('unit_id', 'required'),
			array('unit_id', 'numerical', 'integerOnly'=>true),
		);
	}

    public function prepare($params)
    {
        $params = parent::prepare($params);
        $params['languages'] = I18nActiveRecord::getLangs();
        return $params;
    }
}