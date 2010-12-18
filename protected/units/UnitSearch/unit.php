<?php

class UnitSearch extends Content
{
	const ICON = '/images/icons/fatcow/16x16/find.png';
    const HIDDEN = true;

    public function name($language=null)
    {
        return Yii::t('UnitSearch.unit', 'Search', array(), null, $language);
    }

    public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'units_search';
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
            '{$q}' => Yii::t('UnitSearch.unit', 'Query'),
        );
    }
    public function prepare($params)
    {
        $params = parent::prepare($params);
        $params['q'] = $_GET['q'];
        
        return $params;
    }

    public function content()
    {
        // TODO сделать поиск
        // 1. Собрать все поля string и text для блоков и страниц (в Page, Unit, Unit...)
        // 2. Произвести поиск по полям
        // 3. Вывести результаты
        return Yii::t('UnitSearch.unit', 'Search').': '.$_GET['q'];
    }

}