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
		return Yii::app()->db->tablePrefix . 'widgets_search';
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
            '{$q}' => Yii::t('UnitSearch.main', 'Query'),
        );
    }

    public function content()
    {
        // TODO сделать поиск
        // 1. Собрать все поля string и text для блоков и страниц (в Page, Widget, Widget...)
        // 2. Произвести поиск по полям
        // 3. Вывести результаты
        return Yii::t('UnitSearch.main', 'Search') . ': ' . Yii::app()->request->getQuery('q');
    }

}

