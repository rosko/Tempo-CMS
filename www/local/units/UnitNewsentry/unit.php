<?php

class UnitNewsentry extends Content
{
	const ICON = '/images/icons/fatcow/16x16/newspaper_add.png';
    const HIDDEN = true;

    public function unitName($language=null)
    {
        return Yii::t('UnitNewsentry.unit', 'News entry', array(), null, $language);
    }

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'units_newsentry';
	}

	public function rules()
	{
		return array(
            array('unit_id', 'required', 'on'=>'edit'),
			array('date', 'required'),
			array('unit_id, page_id, news_id', 'numerical', 'integerOnly'=>true),
			array('source', 'length', 'max'=>64, 'encoding'=>'UTF-8'),
            array('image, url', 'length', 'max'=>255, 'encoding'=>'UTF-8'),
            array('text, annotation', 'safe'),
		);
	}

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
//			'unit_id' => 'Unit',
			'text' => Yii::t('UnitNewsentry.unit', 'Content'),
			'date' => Yii::t('UnitNewsentry.unit', 'Date'),
			'source' => Yii::t('UnitNewsentry.unit', 'Source'),
			'url' => Yii::t('UnitNewsentry.unit', 'Link to source'),
            'news_id' => Yii::t('UnitNewsentry.unit', 'News section'),
            'annotation' => Yii::t('UnitNewsentry.unit', 'Annotation'),
            'image' => Yii::t('UnitNewsentry.unit', 'Image'),
		);
	}
/*
    public function i18n()
    {
        return array('text', 'source', 'annotation');
    }
*/
    public function relations()
    {
        return array_merge(parent::relations(), array(
			'section'=>array(self::BELONGS_TO, 'UnitNews', 'news_id'),
        ));
    }

    public static function form()
	{
        $sectionsArray = UnitNews::getSectionsArray();

		return array(
			'elements'=>array(
                Form::tab(Yii::t('UnitNewsentry.unit', 'Annotation')),
				'annotation'=>array(
					'type'=>'TextEditor',
                    'kind'=>'fck',
				),
                'image'=>array(
					'type'=>'Link',
					'size'=>40,
					'showPageSelectButton'=>false,
					'extensions'=>array('jpg', 'jpeg', 'gif', 'png'),
					'onChange'=> "js:$('#cms-pageunit-'+pageUnitId).find('img').attr('src', $(this).val());",
                ),
                Form::tab(Yii::t('UnitNewsentry.unit', 'Entry')),
                'news_id'=> !empty($sectionsArray) ? array(
                    'type'=>'ComboBox',
                    'array'=>$sectionsArray,
                ) : '',
				'date'=>array(
					'type'=>'DateTimePicker',
				),
				'text'=>array(
					'type'=>'TextEditor',
                    'kind'=>'fck',
				),
                Form::tab(Yii::t('UnitNewsentry.unit', 'Source')),
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
            'annotation' => 'text',
            'image' => 'string',
            'text' => 'text',
            'date' => 'datetime',
            'source' => 'string',
            'url' => 'string',
            'page_id' => 'integer unsigned',
            'news_id' => 'integer unsigned',
        );
    }

    public function scopesLabels()
    {
        return array(
            'public' => Yii::t('UnitNewsentry.unit', 'Published only'),
            'imported' => Yii::t('UnitNewsentry.unit', 'With source'),
            'recently' => array(
                Yii::t('UnitNewsentry.unit', 'Recent'),
                'limit' => Yii::t('UnitNewsentry.unit', 'Quantity'),
             ),
            'section' => array(
                Yii::t('UnitNewsentry.unit', 'From section'),
                'news_id' => ''
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
				'news_id'=>array(
					'type'=>'ComboBox',
                    'array'=>UnitNews::getSectionsArray(),
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
			'condition'=>'news_id = :id',
            'params' => array(':id' => $id),
		));
		return $this;
    }

    public function templateVars()
    {
        return array(
            '{$unitUrl}' => Yii::t('UnitNewsentry.unit', 'Link to news entry (in case, when news entry showed as a part of list or news section)'),
            '{$sectionUrl}' => Yii::t('UnitNewsentry.unit', 'Link to news section'),
            '{$sectionTitle}' => Yii::t('UnitNewsentry.unit', 'Name of news section'),
        );
    }

    public function prepare($params)
    {
        $params = parent::prepare($params);
        $params['unitUrl'] = $params['content']->getUnitUrl();
        if ($params['content']->section) {
            $params['sectionUrl'] = $params['content']->section->getUnitUrl();
            $params['sectionTitle'] = $params['content']->section->unit->title;
            
        }
        return $params;
    }

}