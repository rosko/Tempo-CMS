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
                    'type'=>'dropdownlist',
                    'items'=>$newsArray,
                    'prompt'=>'Выберите раздел',
                ) : '',
				'date'=>array(
					'type'=>'DatePicker',
					'language'=>'ru',
					'options'=> array(
						'dateFormat'=>'yy-mm-dd',
//						'showOtherMonths'=>'true',
//						'selectOtherMonths'=>'true'
					)
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
	
}