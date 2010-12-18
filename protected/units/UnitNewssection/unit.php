<?php

class UnitNewssection extends Content
{
	const ICON = '/images/icons/fatcow/16x16/newspaper.png';
    const HIDDEN = false;

    public function name($language=null)
    {
        return Yii::t('UnitNewssection.unit', 'News section', array(), null, $language);
    }

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'units_newssection';
	}

	public function rules()
	{
		return array(
			array('unit_id', 'required'),
			array('unit_id, per_page, items', 'numerical', 'integerOnly'=>true),
		);
	}

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
//			'unit_id' => 'Unit',
			'per_page' => Yii::t('UnitNewssection.unit', 'Entries per page'),
            'items' => '',
		);
	}

	public function relations()
	{
        return array_merge(parent::relations(), array(
			'itemsCount'=>array(self::STAT, 'UnitNewsitem', 'newssection_id'),
		));
	}
    
    public static function form()
	{
		return array(
			'elements'=>array(
                Form::tab(Yii::t('UnitNewssection.unit', 'News section')),
                'items' => array(
                    'type'=>'RecordsGrid',
                    'class_name' => 'UnitNewsitem',
                    'foreign_attribute' => 'newssection_id',
                    'addButtonTitle'=>Yii::t('UnitNewssection.unit', 'Create news entry'),
                    'columns' => array(
                        'date',
                    ),
                    'order' => 'date DESC',
                ),
                Form::tab(Yii::t('UnitNewssection.unit', 'Settings')),
				'per_page'=>array(
					'type'=>'Slider',
					'options'=>array(
						'min' => 0,
						'max' => 25,
					)
				),
                Yii::t('UnitNewssection.unit', 'If zero choosed, accordingly site\'s general settings'),
			),
		);
	}

    public function scheme()
    {
        return array(
            'id' => 'pk',
            'unit_id' => 'integer unsigned',
            'per_page' => 'integer unsigned',
            'items' => 'integer unsigned',
        );
    }

    public function getSectionsArray() {
        $attr = Unit::getI18nFieldName('title', 'Unit');
        $sql = 'SELECT ns.`id`, u.`'.$attr.'` FROM `' . Unit::tableName() .'` as u
                INNER JOIN `' . UnitNewssection::tableName() . '` as ns
                    ON u.id = ns.unit_id
                WHERE u.`type` = "newssection" ORDER BY u.`'.$attr.'`';
        $result = Yii::app()->db->createCommand($sql)->queryAll();
        $ret = array();
        foreach ($result as $row) {
            $ret[$row['id']] = $row[$attr];
        }
        return $ret;
    }

    public function templateVars()
    {
        return array(
            '{$items}' => Yii::t('UnitNewssection.unit', 'News entries'),
            '{$pager}' => Yii::t('UnitNewssection.unit', 'Pager'),
        );
    }

    public function prepare($params)
    {
        $params = parent::prepare($params);
        $items = UnitNewsitem::model()
                    ->public()
                    ->selectPage($params['content']->pageNumber, $params['content']->per_page)
                    ->findAll('newssection_id = :id', array(':id'=>$params['content']->id));
        
        $params['items'] = array();
        foreach ($items as $item)
        {
            $params['items'][] = $item->run(array(
                'in_section'=>true
            ), true);
        }
        $params['pager'] = $params['content']->renderPager(
                count($items),
                $params['content']->itemsCount,
                $params['content']->pageNumber,
                $params['content']->per_page);

        return $params;
    }


}