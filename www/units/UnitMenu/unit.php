<?php

class UnitMenu extends Content
{
	const ICON = '/images/icons/iconic/green/read_more_16x16.png';
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
		return 'units_menu';
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