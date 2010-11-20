<?php

class UnitNewsitem extends Content
{
	const NAME = "Новость";
	const ICON = '/images/icons/iconic/green/comment_alt1_fill_16x16.png';
    const HIDDEN = true;

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'units_newsitem';
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
			'id' => 'ID',
			'unit_id' => 'Unit',
			'text' => 'Текст',
			'date' => 'Дата',
			'source' => 'Название источника',
			'url' => 'Ссылка на источник',
            'newssection_id' => 'Раздел новостей',
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
                Form::tab('Источник новости'),
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

    public function scopesLabels()
    {
        return array(
            'public' => 'Только опубликованные',
            'imported' => 'С указанием источника',
            'recently' => array(
                'Самые свежие',
                'limit' => 'Количество',
             ),
            'section' => array(
                'Из раздела',
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