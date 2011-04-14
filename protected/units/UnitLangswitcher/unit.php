<?php

class UnitLangswitcher extends Content
{
	const ICON = '/images/icons/fatcow/16x16/style_go.png';
    const HIDDEN = false;

    public function name($language=null)
    {
        return Yii::t('UnitLangswitcher.unit', 'Language switcher', array(), null, $language);
    }
    
    public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'units_langswitcher';
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

    public function scheme()
    {
        return array(
            'id' => 'pk',
            'unit_id' => 'integer unsigned',
            'create' => 'datetime',
            'modify' => 'datetime',
        );
    }
    public function cacheVaryBy()
    {
        return array(
            'page_id' => Yii::app()->controller->loadModel()->id,
        );
    }
}