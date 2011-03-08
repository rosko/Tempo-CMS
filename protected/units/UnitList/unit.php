<?php

class UnitList extends Content
{
	const ICON = '/images/icons/fatcow/16x16/newspaper_link.png';
    const HIDDEN = true;

	public function name($language=null)
    {
        return Yii::t('UnitList.unit', 'List', array(), null, $language);
    }

    public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'units_list';
	}

	public function rules()
	{
		return array(
			array('unit_id, class_name', 'required'),
			array('unit_id', 'numerical', 'integerOnly'=>true),
			array('rule', 'length', 'max'=>255, 'encoding'=>'UTF-8'),
		);
	}

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
//			'unit_id' => 'Unit',
            'class_name' => Yii::t('UnitList.unit', 'List type'),
			'rule' => Yii::t('UnitList.unit', 'Which entries to show'),
		);
	}

	public static function form()
	{
		return array(
			'elements'=>array(
                'class_name'=>array(
                    'type'=>'ComboBox',
                    'array'=>Unit::getTypeNames(),
                ),
				'rule'=> array(
                    'type' => 'Scopes',
                    'classNameAttribute' => 'class_name',
                )
			),
		);
	}

    public function scheme()
    {
        return array(
            'id' => 'pk',
            'unit_id' => 'integer unsigned',
            'class_name' => 'char(64)',
            'rule' => 'string',
        );
    }
    public function templateVars()
    {
        return array(
            '{$items}' => Yii::t('UnitList.unit', 'Entries'),
        );
    }

    public function prepare($params)
    {
        $params = parent::prepare($params);
        $params['items'] = array();
        if (Yii::$classMap[$params['content']->class_name]) {
            if ($params['content']->rule)
                $params['content']->rule .= '->';
            if (method_exists($params['content']->class_name, 'public')) {
                $params['content']->rule = 'public()->'.$params['content']->rule;
            }
            eval("\$items = {$params['content']->class_name}::model()->{$params['content']->rule}findAll();");
            foreach ($items as $item)
            {
                $params['items'][] = $item->run(array(), true);
            }
        }
        return $params;
    }

}