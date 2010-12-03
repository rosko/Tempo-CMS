<?php

class UnitNewslist extends Content
{
	const ICON = '/images/icons/iconic/green/document_fill_16x16.png';
    const HIDDEN = true;

	public function name()
    {
        return Yii::t('UnitNewslist.unit', 'Newslist');
    }

    public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'units_newslist';
	}

	public function rules()
	{
		return array(
			array('unit_id', 'required'),
			array('unit_id', 'numerical', 'integerOnly'=>true),
			array('rule', 'length', 'max'=>255),
		);
	}

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
//			'unit_id' => 'Unit',
			'rule' => Yii::t('UnitNewslist.unit', 'Which news to show'),
		);
	}

	public static function form()
	{
		return array(
			'elements'=>array(
				'rule'=> array(
                    'type' => 'Scopes',
                    'className' => 'UnitNewsitem'
                )
			),
		);
	}

    public function templateVars()
    {
        return array(
            '{$items}' => Yii::t('UnitNewslist.unit', 'News entries'),
        );
    }

    public function prepare($params)
    {
        $params = parent::prepare($params);
        if ($params['content']->rule)
            $params['content']->rule .= '->';
        eval("\$items = UnitNewsitem::model()->public()->{$params['content']->rule}findAll();");
        $params['items'] = array();
        foreach ($items as $item)
        {
            $params['items'][] = $item->run(array(), true);
        }
        return $params;
    }

}