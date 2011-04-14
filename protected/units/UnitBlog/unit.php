<?php

class UnitBlog extends Content
{
	const ICON = '/images/icons/fatcow/16x16/newspaper.png';
    const HIDDEN = false;

    public function name($language=null)
    {
        return Yii::t('UnitBlog.unit', 'Blog/news section', array(), null, $language);
    }

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'units_blog';
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
			'per_page' => Yii::t('UnitBlog.unit', 'Entries per page'),
            'items' => '',
		);
	}

	public function relations()
	{
        return array_merge(parent::relations(), array(
			'itemsCount'=>array(self::STAT, 'UnitBlogentry', 'blog_id'),
		));
	}
    
    public static function form()
	{
		return array(
			'elements'=>array(
                Form::tab(Yii::t('UnitBlog.unit', 'Blog/news section')),
                'items' => array(
                    'type'=>'RecordsGrid',
                    'class_name' => 'UnitBlogentry',
                    'foreign_attribute' => 'blog_id',
                    'addButtonTitle'=>Yii::t('UnitBlog.unit', 'Create entry'),
                    'columns' => array(
                        'date',
                    ),
                    'order' => 'date DESC',
                ),
                Form::tab(Yii::t('UnitBlog.unit', 'Settings')),
				'per_page'=>array(
					'type'=>'Slider',
                    'hint'=>Yii::t('UnitBlog.unit', 'If zero choosed, accordingly site\'s general settings'),
					'options'=>array(
						'min' => 0,
						'max' => 25,
					)
				),                
			),
		);
	}

    public function scheme()
    {
        return array(
            'id' => 'pk',
            'unit_id' => 'integer unsigned',
            'create' => 'datetime',
            'modify' => 'datetime',
            'per_page' => 'integer unsigned',
            'items' => 'integer unsigned',
        );
    }

    public function getSectionsArray() {
        $attr = Unit::getI18nFieldName('title', 'Unit');
        $sql = 'SELECT ns.`id`, u.`'.$attr.'` FROM `' . Unit::tableName() .'` as u
                INNER JOIN `' . UnitBlog::tableName() . '` as ns
                    ON u.id = ns.unit_id
                WHERE u.`type` = "blog" ORDER BY u.`'.$attr.'`';
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
            '{$items}' => Yii::t('UnitBlog.unit', 'Еntries'),
            '{$pager}' => Yii::t('UnitBlog.unit', 'Pager'),
        );
    }

    public function  cacheDependencies() {
        return array(
            array(
                'class'=>'system.caching.dependencies.CDbCacheDependency',
                'sql'=>'SELECT MAX(modify) FROM `' . UnitBlogentry::tableName() . '` WHERE blog_id = :id',
                'params' => array(
                    'id' => $this->id
                ),
            ),
        );
    }

    public function prepare($params)
    {
        $params = parent::prepare($params);
        $items = UnitBlogentry::model()
                    ->public()
                    ->selectPage($params['content']->pageNumber, $params['content']->per_page)
                    ->findAll('blog_id = :id', array(':id'=>$params['content']->id));
        
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