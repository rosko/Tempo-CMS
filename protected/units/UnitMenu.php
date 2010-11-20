<?php

class UnitMenu extends Content
{
	const NAME = "Меню";
	const ICON = '/images/icons/iconic/green/read_more_16x16.png';
    const HIDDEN = true;

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
			'id' => 'ID',
			'unit_id' => 'Unit',
			'items' => 'Items',
            'recursive' => 'Количество уровней',
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
                'Если выбран 0, то отображаются соседние страницы',
/*				'items'=>array(
					'type'=>'textarea',
					'rows'=>7,
					'cols'=>40
				),*/
			),
		);
	}

    public function prepare($params)
    {
        $params = parent::prepare($params);
        $params['tree'] = Page::model()->getTree();

        return $params;
    }

}