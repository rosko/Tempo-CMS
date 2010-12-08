<?php

class UnitSitemap extends Content
{
	const ICON = '/images/icons/fatcow/16x16/sitemap_color.png';
    const HIDDEN = true;

    public function name()
    {
        return Yii::t('UnitSitemap.unit', 'Sitemap');
    }

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
			'length' => Yii::t('UnitSitemap.unit', 'Descriptions\' length'),
            'recursive' => Yii::t('UnitSitemap.unit', 'Levels depth'),
            'page_id' => Yii::t('UnitSitemap.unit', 'Parent page'),
            'show_title' => Yii::t('UnitSitemap.unit', 'Show header'),
			'per_page' => Yii::t('UnitSitemap.unit', 'Entries per page'),
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
                Yii::t('UnitSitemap.unit', 'If zero choosed, siblings pages will show'),
				'per_page'=>array(
					'type'=>'Slider',
					'options'=>array(
						'min' => 0,
						'max' => 25,
					)
				),
                Yii::t('UnitSitemap.unit', 'If zero choosed, accordingly site\'s general settings'),
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
            $items = Page::model()->order()->selectPage($params['content']->pageNumber, $params['content']->per_page)->childrenPages($id)->localized()->getAll();
        else
            $items = Page::model()->order()->childrenPages($id)->localized()->getAll();
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
            '{$title}' => Yii::t('UnitSitemap.unit', 'Header'),
            '{$items}' => Yii::t('UnitSitemap.unit', 'Entries'),
            '{$count_items}' => Yii::t('UnitSitemap.unit', 'Entries quantity'),
            '{$pager}' => Yii::t('UnitSitemap.unit', 'Pager'),
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