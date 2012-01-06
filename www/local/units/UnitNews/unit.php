<?php

class UnitNews extends ContentModel
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
        return Yii::t('UnitNews.main', 'News section', array(), null, $language);
    }

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'units_news';
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
			'per_page' => Yii::t('UnitNews.main', 'Entries per page'),
            'items' => '',
		);
	}

	public function relations()
	{
        return array_merge(parent::relations(), array(
			'itemsCount'=>array(self::STAT, 'UnitNewsentry', 'news_id'),
		));
	}
    
    public static function form()
	{
		return array(
			'elements'=>array(
                Form::tab(Yii::t('UnitNews.main', 'News section')),
                'items' => array(
                    'type'=>'RecordsGrid',
                    'className' => 'UnitNewsentry',
                    'foreignAttribute' => 'news_id',
                    'addButtonTitle'=>Yii::t('UnitNews.main', 'Create entry'),
                    'columns' => array(
                        'date',
                    ),
                    'order' => 'date DESC',
                ),
                Form::tab(Yii::t('UnitNews.main', 'Settings')),
				'per_page'=>array(
					'type'=>'Slider',
                    'hint'=>Yii::t('UnitNews.main', 'If zero choosed, accordingly site\'s general settings'),
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
                INNER JOIN `' . UnitNews::tableName() . '` as ns
                    ON u.id = ns.unit_id
                WHERE u.`type` = "news" ORDER BY u.`'.$attr.'`';
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
            '{$items}' => Yii::t('UnitNews.main', 'Еntries'),
            '{$pager}' => Yii::t('UnitNews.main', 'Pager'),
        );
    }

    public function  cacheDependencies() {
        return array(
            array(
                'class'=>'system.caching.dependencies.CDbCacheDependency',
                'sql'=>'SELECT MAX(`modify`) FROM `' . UnitNewsentry::tableName() . '` WHERE news_id = :id',
                'params' => array(
                    'id' => $this->id
                ),
            ),
        );
    }

}

class UnitNewsWidget extends ContentWidget
{
    public function init()
    {
        parent::init();
        $items = UnitNewsentry::model()
                    ->public()
                    ->selectPage($this->params['content']->pageNumber, $this->params['content']->per_page)
                    ->with('unit')
                    ->findAll('news_id = :id', array(':id'=>$this->params['content']->id));
        
        $this->params['items'] = array();
        foreach ($items as $item)
        {
            $this->params['items'][] = $item->widget('UnitNewsentry', array(
                'in_section'=>true,
            ), true);
        }
        $this->params['pager'] = $this->params['content']->renderPager(
                count($items),
                $this->params['content']->itemsCount,
                $this->params['content']->pageNumber,
                $this->params['content']->per_page);

   }
}