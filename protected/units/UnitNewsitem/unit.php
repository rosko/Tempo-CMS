<?php

class UnitNewsitem extends Content
{
	const ICON = '/images/icons/fatcow/16x16/newspaper_add.png';
    const HIDDEN = true;

    public function name($language=null)
    {
        return Yii::t('UnitNewsitem.unit', 'News entry', array(), null, $language);
    }

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'units_newsitem';
	}

	public function rules()
	{
		return array(
			array('unit_id, text, date', 'required'),
			array('unit_id, page_id, newssection_id', 'numerical', 'integerOnly'=>true),
			array('source', 'length', 'max'=>64),
			array('url', 'length', 'max'=>255),
		);
	}

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
//			'unit_id' => 'Unit',
			'text' => Yii::t('UnitNewsitem.unit', 'Content'),
			'date' => Yii::t('UnitNewsitem.unit', 'Date'),
			'source' => Yii::t('UnitNewsitem.unit', 'Source'),
			'url' => Yii::t('UnitNewsitem.unit', 'Link to source'),
            'newssection_id' => Yii::t('UnitNewsitem.unit', 'News section'),
		);
	}

    public function relations()
    {
        return array_merge(parent::relations(), array(
			'section'=>array(self::BELONGS_TO, 'UnitNewssection', 'newssection_id'),
        ));
    }

	public static function form()
	{
        $newsArray = UnitNewssection::getSectionsArray();

		return array(
			'elements'=>array(
                Form::tab('Новость'),
				'text'=>array(
					'type'=>'VisualTextAreaFCK',
				),
                'newssection_id'=> !empty($newsArray) ? array(
                    'type'=>'ComboBox',
                    'array'=>$newsArray,
                ) : '',
				'date'=>array(
					'type'=>'DateTimePicker',
				),
                Form::tab(Yii::t('UnitNewsitem.unit', 'News source')),
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
            'text' => 'text',
            'date' => 'datetime',
            'source' => 'string',
            'url' => 'string',
            'page_id' => 'integer unsigned',
            'newssection_id' => 'integer unsigned',
        );
    }

    public function scopesLabels()
    {
        return array(
            'public' => Yii::t('UnitNewsitem.unit', 'Published only'),
            'imported' => Yii::t('UnitNewsitem.unit', 'With source'),
            'recently' => array(
                Yii::t('UnitNewsitem.unit', 'Recent'),
                'limit' => Yii::t('UnitNewsitem.unit', 'Quantity'),
             ),
            'section' => array(
                Yii::t('UnitNewsitem.unit', 'From news section'),
                'newssection_id' => ''
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
				'newssection_id'=>array(
					'type'=>'ComboBox',
                    'array'=>UnitNewssection::getSectionsArray(),
				)
			)
		);
	}

	public function recently($limit=5)
	{
		$this->getDbCriteria()->mergeWith(array(
			'limit'=>$limit
		));
		return $this;
	}
	
    public function section($id=0)
    {
		$this->getDbCriteria()->mergeWith(array(
			'condition'=>'newssection_id = :id',
            'params' => array(':id' => $id),
		));
		return $this;
    }

    public function templateVars()
    {
        return array(
            '{$unitUrl}' => Yii::t('UnitNewsitem.unit', 'Link to news entry (in case, when news entry showed as a part of list or news section)'),
            '{$sectionUrl}' => Yii::t('UnitNewsitem.unit', 'Link to news section'),
            '{$sectionTitle}' => Yii::t('UnitNewsitem.unit', 'Name of news section'),
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