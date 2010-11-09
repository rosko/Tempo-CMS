<?php

class UnitNewssection extends Content
{
	const NAME = "Раздел новостей";
	const ICON = '/images/icons/iconic/cyan/document_fill_16x16.png';
    const HIDDEN = false;

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'units_newssection';
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
			'id' => 'ID',
			'unit_id' => 'Unit',
			'per_page' => 'Новостей на одной странице',
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
                Form::tab('Раздел новостей'),
                'items' => array(
                    'type'=>'RecordsGrid',
                    'className' => 'UnitNewsitem',
                    'foreignAttribute' => 'newssection_id',
                    'columns' => array(
                        'date',
                    ),
                    'order' => 'date DESC',
                ),
                Form::tab('Настройки'),
				'per_page'=>array(
					'type'=>'Slider',
					'options'=>array(
						'min' => 0,
						'max' => 25,
					)
				),
                'Если выбран 0, то согласно общих настроек сайта.',
			),
		);
	}

    public function getSectionsArray() {
        $sql = 'SELECT ns.id, u.title FROM `' . Unit::tableName() .'` as u
                INNER JOIN `' . UnitNewssection::tableName() . '` as ns
                    ON u.id = ns.unit_id
                WHERE u.`type` = "newssection" ORDER BY u.`title`';
        $result = Yii::app()->db->createCommand($sql)->queryAll();
        $ret = array();
        foreach ($result as $row) {
            $ret[$row['id']] = $row['title'];
        }
        return $ret;
    }

}