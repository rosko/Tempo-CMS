<?php

class UnitBreadcrumbs extends Content
{
	const NAME = "Путь к странице";
	const ICON = '/images/icons/iconic/green/read_more_16x16.png';
    const HIDDEN = true;

    const DEFAULT_SEPARATOR = ' &raquo; ';

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'units_breadcrumbs';
	}

	public function rules()
	{
		return array(
			array('unit_id', 'required'),
			array('unit_id', 'numerical', 'integerOnly'=>true),
			array('separator', 'length', 'max'=>16),
		);
	}

	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'unit_id' => 'Unit',
            'separator'=> 'Разделитель'
		);
	}

	public static function form()
	{
		return array(
			'elements'=>array(
                'separator'=>array(
                    'type'=>'text'
                ),
                'Если пусто, используется \'<b>' . UnitBreadcrumbs::DEFAULT_SEPARATOR . '</b>\'',
			),
		);
	}

    public function prepare($params)
    {
        $params = parent::prepare($params);

        $ids = explode(',', $params['page']->path);
        $pages = Page::model()->findAll(array(
            'condition' => '`id` IN ('.$params['page']->path.')',
            'order' => '`path` DESC'
        ));
        $parents = array();
        foreach ($pages as $p) {
            $parents[$p->id] = $p;
        }
        unset($pages);

        $links = array();
        foreach ($ids as $id) {
            if ($id == 0 || $id == 1) continue;
            $links[$parents[$id]->title] = array('page/view', 'id'=>$parents[$id]->id);
        }
        if ($params['page']->id != 1)
            $links[] = $params['page']->title;
        else
            $links[] = '';
        $params['links'] = $links;

        $params['separator'] = $params['content']->separator ? $params['content']->separator : self::DEFAULT_SEPARATOR;

        $params['homeLink'] = ($parents ? CHtml::link($parents[1]->title, array('page/view', 'id'=>$parents[1]->id)) : $params['page']->title);

        return $params;
    }
}