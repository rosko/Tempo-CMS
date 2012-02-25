<?php

class ModelBlog_Entry extends ContentModel
{
    public function icon()
    {
        return '/images/icons/fatcow/16x16/newspaper_add.png';
    }

    public function modelName($language=null)
    {
        return Yii::t('UnitBlog.main', 'Blog/news entry', array(), null, $language);
    }

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'blogentries';
	}

	public function rules()
	{
		return $this->localizedRules(array(
			array('title', 'required'),
			array('blog_id', 'numerical', 'integerOnly'=>true),
			array('source', 'length', 'max'=>64, 'encoding'=>'UTF-8'),
            array('title, image, url', 'length', 'max'=>255, 'encoding'=>'UTF-8'),
            array('date, text, annotation', 'safe'),
		));
	}

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
            'title' => Yii::t('UnitBlog.main', 'Title'),
			'text' => Yii::t('UnitBlog.main', 'Content'),
			'date' => Yii::t('UnitBlog.main', 'Date'),
			'source' => Yii::t('UnitBlog.main', 'Source'),
			'url' => Yii::t('UnitBlog.main', 'Link to source'),
            'blog_id' => Yii::t('UnitBlog.main', 'Blog/news section'),
            'annotation' => Yii::t('UnitBlog.main', 'Annotation'),
            'image' => Yii::t('UnitBlog.main', 'Image'),
		);
	}

    public function i18n()
    {
        return array('title', 'text', 'source', 'annotation', 'image');
    }

    public function relations()
    {
        return array_merge(parent::relations(), array(
			'section'=>array(self::BELONGS_TO, 'ModelBlog', 'blog_id'),
        ));
    }

    public static function form()
	{
        $sectionsArray = ModelBlog::getSectionsArray();
        
		return array(
			'elements'=>array(
                Form::tab(Yii::t('UnitBlog.main', 'Annotation')),
                'title'=>array(
                    'type'=>'text',
                    'size'=>40,
                ),
				'annotation'=>array(
					'type'=>'TextEditor',
                    'kind'=>'ck',
                    'config'=>array(
                        'toolbar'=>'Basic',
                    ),
				),
                'image'=>array(
					'type'=>'Link',
					'size'=>40,
					'showPageSelectButton'=>false,
					'extensions'=>array('jpg', 'jpeg', 'gif', 'png'),
					'onChange'=> "js:$('#cms-pageunit-'+pageUnitId).find('img').attr('src', $(this).val());",
                ),
                Form::tab(Yii::t('UnitBlog.main', 'Entry')),
                'blog_id'=> !empty($sectionsArray) ? array(
                    'type'=>'ComboBox',
                    'array'=>$sectionsArray,
                ) : '',
				'date'=>array(
//					'type'=>'DateTimePicker',
                    'type'=>'text',
				),
				'text'=>array(
					'type'=>'TextEditor',
                    'kind'=>'ck',
                    'config'=>array(
                        'toolbar'=>'Basic',
                    ),
				),
                Form::tab(Yii::t('UnitBlog.main', 'Source')),
				'source'=>array(
					'type'=>'text',
					'maxlength'=>64
				),
				'url'=>array(
					'type'=>'Link',
                    'showFileManagerButton'=>false,
                    'showUploadButton'=>false
					//'maxlength'=>255
				),                
			),
		);
	}

    public function scheme()
    {
        return array(
            'title' => 'string',
            'annotation' => 'text',
            'image' => 'string',
            'text' => 'text',
            'date' => 'timestamp',
            'source' => 'string',
            'url' => 'string',
            'blog_id' => 'integer unsigned',
        );
    }

    public function scopesLabels()
    {
        return array(
            'public' => Yii::t('UnitBlog.main', 'Published only'),
            'imported' => Yii::t('UnitBlog.main', 'With source'),
            'recently' => array(
                Yii::t('UnitBlog.main', 'Recent'),
                'limit' => Yii::t('UnitBlog.main', 'Quantity'),
             ),
            'section' => array(
                Yii::t('UnitBlog.main', 'From section'),
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
                    'array'=>ModelBlog::getSectionsArray(),
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
            '{$unitUrl}' => Yii::t('UnitBlog.main', 'Link to blog/news entry (in case, when blog/news entry showed as a part of list or blog/news section)'),
            '{$sectionUrl}' => Yii::t('UnitBlog.main', 'Link to blog/news section'),
            '{$sectionTitle}' => Yii::t('UnitBlog.main', 'Name of blog/news section'),
        );
    }

    public function beforeSave()
    {
        if ($this->hasAttribute('date') && !$this->date) {
            $this->date = new CDbExpression('NOW()');
        }
        return parent::beforeSave();
    }
}
