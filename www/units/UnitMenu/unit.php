<?php

class UnitMenu extends Content
{
	const ICON = '/images/icons/fatcow/16x16/breeze.png';
    const HIDDEN = true;

    public function name()
    {
        return Yii::t('UnitMenu.unit', 'Menu');
    }

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'units_menu';
	}

	public function rules()
	{
		return array(
			array('unit_id', 'required'),
			array('unit_id, recursive', 'numerical', 'integerOnly'=>true),
		);
	}

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
//			'unit_id' => 'Unit',
//			'items' => 'Items',
            'recursive' => Yii::t('UnitMenu.unit', 'Levels'),
		);
	}

	public static function form()
	{
		return array(
			'elements'=>array(
				'recursive'=>array(
					'type'=>'Slider',
					'options'=>array(
						'min' => 1,
						'max' => 10,
					)
				),
                Yii::t('UnitMenu.unit', 'If zero choosed, siblings pages will show'),
/*				'items'=>array(
					'type'=>'textarea',
					'rows'=>7,
					'cols'=>40
				),*/
			),
		);
	}

    public function scheme()
    {
        return array(
            'id' => 'pk',
            'unit_id' => 'integer unsigned',
            'recursive' => 'integer unsigned',
            'items' => 'text',
        );
    }

    public function templateVars()
    {
        return array(
            '{$tree}'=>Yii::t('UnitMenu.unit', 'Menu items'),
        );
    }

    public function prepare($params)
    {
        $params = parent::prepare($params);
        $params['tree'] = Page::model()->getTree();

        return $params;
    }

}