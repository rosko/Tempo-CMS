<?php

class UnitBlogentry extends ContentModel
{
    public function icon()
    {
        return '/images/icons/fatcow/16x16/newspaper_add.png';
    }
    
    public function hidden()
    {
        return true;
    }
    
    public function unitName($language=null)
    {
        return Yii::t('UnitBlogentry.main', 'Blog/news entry', array(), null, $language);
    }

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'units_blogentry';
	}

	public function rules()
	{
		return array(
            array('unit_id', 'required', 'on'=>'edit'),
			array('text, date', 'required'),
			array('unit_id, page_id, blog_id', 'numerical', 'integerOnly'=>true),
			array('source', 'length', 'max'=>64, 'encoding'=>'UTF-8'),
			array('url', 'length', 'max'=>255, 'encoding'=>'UTF-8'),
		);
	}

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
//			'unit_id' => 'Unit',
			'text' => Yii::t('UnitBlogentry.main', 'Content'),
			'date' => Yii::t('UnitBlogentry.main', 'Date'),
			'source' => Yii::t('UnitBlogentry.main', 'Source'),
			'url' => Yii::t('UnitBlogentry.main', 'Link to source'),
            'blog_id' => Yii::t('UnitBlogentry.main', 'Blog/news section'),
		);
	}

    public function relations()
    {
        return array_merge(parent::relations(), array(
			'section'=>array(self::BELONGS_TO, 'UnitBlog', 'blog_id'),
        ));
    }

    public static function form()
	{
        $sectionsArray = UnitBlog::getSectionsArray();

		return array(
			'elements'=>array(
                Form::tab(Yii::t('UnitBlogentry.main', 'Entry')),
				'text'=>array(
					'type'=>'TextEditor',
                    'kind'=>'fck',
				),
                'blog_id'=> !empty($sectionsArray) ? array(
                    'type'=>'ComboBox',
                    'array'=>$sectionsArray,
                ) : '',
				'date'=>array(
					'type'=>'DateTimePicker',
				),
                Form::tab(Yii::t('UnitBlogentry.main', 'Source')),
				'source'=>array(
					'type'=>'text',
					'maxlength'=>64
				),
				'url'=>array(
					'type'=>'Link',
                    'showFileManagerButton'=>false,
                    'showUploadButton'=>false
					//'maxlength'=>255
				)
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
            'text' => 'text',
            'date' => 'datetime',
            'source' => 'string',
            'url' => 'string',
            'page_id' => 'integer unsigned',
            'blog_id' => 'integer unsigned',
        );
    }

    public function scopesLabels()
    {
        return array(
            'public' => Yii::t('UnitBlogentry.main', 'Published only'),
            'imported' => Yii::t('UnitBlogentry.main', 'With source'),
            'recently' => array(
                Yii::t('UnitBlogentry.main', 'Recent'),
                'limit' => Yii::t('UnitBlogentry.main', 'Quantity'),
             ),
            'section' => array(
                Yii::t('UnitBlogentry.main', 'From section'),
                'blog_id' => ''
            )
        );
    }
	
	public function scopes()
	{
		return array(
			'public' => array(
				'order'=>'date DESC',
				'condition'=>'date <= NOW()'
			),
			'imported'=>array(
				'condition'=>'source <> "" OR url <> ""'
			),
		);
	}

    public function hiddenScopes()
    {
        return array('public');
    }
	
	public function namedScopes()
	{
		return array(
			'recently'=>array(
				'limit'=>array(
					'type'=>'Slider',
					'options'=>array(
						'min' => 1,
						'max' => 20,
					)
				)
            ),
			'section'=>array(
				'blog_id'=>array(
					'type'=>'ComboBox',
                    'array'=>UnitBlog::getSectionsArray(),
				)
			)
		);
	}

	public function recently($limit=5)
	{
		$this->getDbCriteria()->mergeWith(array(
			'limit'=>$limit,
            'order'=>'date DESC',
		));
		return $this;
	}
	
    public function section($id=0)
    {
		$this->getDbCriteria()->mergeWith(array(
			'condition'=>'blog_id = :id',
            'params' => array(':id' => $id),
		));
		return $this;
    }

    public function feedItem()
    {
        return array(
            'title'=>null, // тогда используется unit->title,
            'description'=>'text',
            'updated'=>'date',
        );
    }

    public function templateVars()
    {
        return array(
            '{$unitUrl}' => Yii::t('UnitBlogentry.main', 'Link to blog/news entry (in case, when blog/news entry showed as a part of list or blog/news section)'),
            '{$sectionUrl}' => Yii::t('UnitBlogentry.main', 'Link to blog/news section'),
            '{$sectionTitle}' => Yii::t('UnitBlogentry.main', 'Name of blog/news section'),
        );
    }

}

class UnitBlogentryWidget extends ContentWidget
{
    public function init()
    {
        parent::init();
        $this->params['unitUrl'] = $this->params['content']->getUnitUrl();
        if ($this->params['content']->section) {
            $this->params['sectionUrl'] = $this->params['content']->section->getUnitUrl();
            $this->params['sectionTitle'] = $this->params['content']->section->unit->title;
            
        }    
    }    
}