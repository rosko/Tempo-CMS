<?php

class ModelSearch extends ContentModel
{
    public function icon()
    {
        return '/images/icons/fatcow/16x16/find.png';
    }
    
    public function modelName($language=null)
    {
        return Yii::t('UnitSearch.main', 'Search', array(), null, $language);
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
            '{$q}' => Yii::t('UnitSearch.main', 'Query'),
        );
    }

    public function content()
    {
        // TODO сделать поиск
        // 1. Собрать все поля string и text для блоков и страниц (в Page, Unit, Unit...)
        // 2. Произвести поиск по полям
        // 3. Вывести результаты
        return Yii::t('UnitSearch.main', 'Search').': '.$_GET['q'];
    }

}

