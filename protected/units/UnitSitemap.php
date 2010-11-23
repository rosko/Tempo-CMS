<?php

class UnitSitemap extends Content
{
	const NAME = "Карта сайта";
	const ICON = '/images/icons/iconic/green/read_more_16x16.png';
    const HIDDEN = true;

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'units_sitemap';
	}

	public function rules()
	{
		return array(
			array('unit_id', 'required'),
			array('unit_id, length, recursive, page_id, per_page', 'numerical', 'integerOnly'=>true),
            array('show_title', 'boolean'),
		);
	}

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
//			'unit_id' => 'Unit',
			'length' => 'Длина описания',
            'recursive' => 'Количество уровней',
            'page_id' => 'Страница',
            'show_title' => 'Отображать заголовок',
			'per_page' => 'Объектов на одну страницу',
		);
	}

	public static function form()
	{
		return array(
			'elements'=>array(
                'show_title'=>array(
                    'type'=>'checkbox',
                ),
				'length'=>array(
					'type'=>'Slider',
					'options'=>array(
						'min' => 1,
						'max' => 2000,
						'step' => 50,
					)
				),
				'recursive'=>array(
					'type'=>'Slider',
					'options'=>array(
						'min' => 0,
						'max' => 10,
					)
				),
                'Если выбран 0, то отображаются соседние страницы',
				'per_page'=>array(
					'type'=>'Slider',
					'options'=>array(
						'min' => 0,
						'max' => 25,
					)
				),
                'Если выбран 0, то согласно общих настроек сайта.',
				'page_id'=>array(
					'type'=>'PageSelect',
                    'excludeCurrent'=>false,
				),
			),
		);
	}

    public static function getTree($id, $params, $recursive=0, $start=false)
    {
        if ($start)
            $items = Page::model()->order()->selectPage($params['content']->pageNumber, $params['content']->per_page)->childrenPages($id)->getAll();
        else
            $items = Page::model()->order()->childrenPages($id)->getAll();
        if ($recursive > 1) {
            foreach ($items as $k => $item)
            {
                $items[$k]['children'] = self::getTree($item['id'], $params, $recursive-1);
            }
        }
        return $items;
    }

    public function templateVars()
    {
        return array(
            '{$title}' => 'Заголовок',
            '{$items}' => 'Массив пунктов',
            '{$count_items}' => 'Количество пунктов',
            '{$pager}' => 'Список страниц (если производится разбиение списка на страницы)',
        );
    }

    public function prepare($params)
    {
        $params = parent::prepare($params);
        $model = $params['content']->page_id ? Page::model()->findByPk($params['content']->page_id) : $params['page'];
        $params['title'] = $params['unit']->title ? $params['unit']->title : $model->title;

        $id = $params['content']->recursive ? $model->id : $model->parent_id;
        $params['items'] = array();
        if ($id)
            $params['items'] = self::getTree($id, $params, $params['content']->recursive, true);

        $params['count_items'] = count($params['items']);

        $params['pager'] = $params['content']->renderPager(
                $params['count_items'],
                $model->childrenCount,
                $params['content']->pageNumber,
                $params['content']->per_page
        );
        return $params;
    }


}