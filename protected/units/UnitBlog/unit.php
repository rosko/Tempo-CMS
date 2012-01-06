<?php

class UnitBlog extends ContentModel
{
    public function icon()
    {
        return '/images/icons/fatcow/16x16/newspaper.png';
    }
    
    public function hidden()
    {
        return false;
    }

    public function unitName($language=null)
    {
        return Yii::t('UnitBlog.main', 'Blog/news section', array(), null, $language);
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
            array('unit_id', 'required', 'on'=>'edit'),
			array('unit_id, per_page, items', 'numerical', 'integerOnly'=>true),
		);
	}

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
//			'unit_id' => 'Unit',
			'per_page' => Yii::t('UnitBlog.main', 'Entries per page'),
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
                Form::tab(Yii::t('UnitBlog.main', 'Blog/news section')),
                'items' => array(
                    'type'=>'RecordsGrid',
                    'makePage'=>true,
                    'className' => 'UnitBlogentry',
                    'foreignAttribute' => 'blog_id',
                    'addButtonTitle'=>Yii::t('UnitBlog.main', 'Create entry'),
                    'columns' => array(
                        'date',
                    ),
                    'order' => 'date DESC',
                ),
                Form::tab(Yii::t('UnitBlog.main', 'Settings')),
				'per_page'=>array(
					'type'=>'Slider',
                    'hint'=>Yii::t('UnitBlog.main', 'If zero choosed, accordingly site\'s general settings'),
					'options'=>array(
						'min' => 0,
						'max' => 25,
					)
				),                
			),
		);
	}

    public function feed()
    {
        return array(
            'title'=>null, // тогда используется unit->title
            'description'=>null, // тогда используется unit->title
            'author'=>null, // тогда не используется, а можно указать имя поля с id автора (User)
            'items'=>array(
                'UnitBlogentry',
                'condition'=>'`blog_id` = :id AND `date` <= NOW()',
                'order'=>'`date` DESC',
                'params'=>array('id'),
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
            '{$items}' => Yii::t('UnitBlog.main', 'Еntries'),
            '{$pager}' => Yii::t('UnitBlog.main', 'Pager'),
        );
    }

    public function cacheVaryBy()
    {
        return array(
            '_GET' => $_GET,
        );
    }

    public function  cacheDependencies() {
        return array(
            array(
                'class'=>'system.caching.dependencies.CDbCacheDependency',
                'sql'=>'SELECT MAX(`modify`) FROM `' . UnitBlogentry::tableName() . '` WHERE blog_id = :id',
                'params' => array(
                    'id' => $this->id
                ),
            ),
        );
    }

}

class UnitBlogWidget extends ContentWidget
{
    public function init()
    {
        parent::init();
        $items = UnitBlogentry::model()
                    ->public()
                    ->with('unit')
                    ->selectPage($this->params['content']->pageNumber, $this->params['content']->per_page)
                    ->findAll('blog_id = :id', array(':id'=>$this->params['content']->id));
        
        $this->params['items'] = array();
        foreach ($items as $item)
        {
            $this->params['items'][] = $item->widget('UnitBlogentry', array(
                'in_section'=>true
            ), true);
        }
        $this->params['pager'] = $this->params['content']->renderPager(
                count($items),
                $this->params['content']->itemsCount,
                $this->params['content']->pageNumber,
                $this->params['content']->per_page);
    }

    
}