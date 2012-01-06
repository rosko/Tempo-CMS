<?php

class UnitRandomimage extends ContentModel
{
    public function icon()
    {
        return '/images/icons/fatcow/16x16/image.png';
    }
    
    public function hidden()
    {
        return false;
    }
    
    public function cacheable()
    {
        return false;
    }
    
    public function unitName($language=null)
    {
        return Yii::t('UnitRandomimage.main', 'Random image', array(), null, $language);
    }

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'units_randomimage';
	}

	public function rules()
	{
		return array(
            array('unit_id', 'required', 'on'=>'edit'),
			array('images, width, height', 'required'),
			array('unit_id, width, height', 'numerical', 'integerOnly'=>true),
            array('images', 'type', 'type'=>'array'),
			array('url', 'length', 'max'=>255, 'encoding'=>'UTF-8'),
            array('target', 'length', 'max'=>50, 'encoding'=>'UTF-8')
		);
	}

	public function attributeLabels()
	{
		return array(
			'images' => Yii::t('UnitRandomimage.main', 'Images'),
			'width' => Yii::t('UnitRandomimage.main', 'Width'),
			'height' => Yii::t('UnitRandomimage.main', 'Height'),
			'url' => Yii::t('UnitRandomimage.main', 'Link'),
            'target' => Yii::t('UnitRandomimage.main', 'Link target'),
		);
	}

    public function templateVars()
    {
        return array(
            '{$image}' => Yii::t('UnitRandomimage.main', 'Random-selected image'),
        );
    }

    public function behaviors()
    {
        return array(
            'CSerializeBehavior' => array(
                'class' => 'application.behaviors.CSerializeBehavior',
                'serialAttributes' => array('images'),
            )
        );
    }

	public static function form()
	{
		return array(
			'elements'=>array(
                Form::tab(Yii::t('UnitRandomimage.main', 'Images')),
				'images'=>array(
					'type'=>'ListEdit',
					//'size'=>40,
					//'showPageSelectButton'=>false,
					//'extensions'=>array('jpg', 'jpeg', 'gif', 'png'),
				),
				'width'=>array(
					'type'=>'Slider',
					'event'=>'none',
					'options'=>array(
						'min' => 1,
						'max' => 2000,
						'step' => 1,
					)
				),
				'height'=>array(
					'type'=>'Slider',
					'event'=>'none',
					'options'=>array(
						'min' => 1,
						'max' => 2000,
						'step' => 1,
					)
				),
                Form::tab(Yii::t('UnitRandomimage.main', 'Link')),
				'url'=>array(
					'type'=>'Link',
					'size'=>40,
					'showUploadButton'=>false
				),
                'target'=>array(
                    'type'=>'dropdownlist',
                    'items'=> array(
                        '' => Yii::t('UnitRandomimage.main', 'Current window'),
                        '_blank' => Yii::t('UnitRandomimage.main', 'New window'),
                    ),
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
            'images' => 'text',
            'width' => 'integer unsigned',
            'height' => 'integer unsigned',
            'url' => 'string',
            'target' => 'char(32)',
        );
    }

    public static function defaultObject()
	{
		$obj = new self;
		$obj->images = array('image');
		$obj->width = 100;
		$obj->height = 100;
		return $obj;
	}

    public function resizableObjects()
    {
        return array(
            'img' => array(
                'attributes' => array('width', 'height'),
                'aspectRatio' => false,
            ),
        );
    }

}


class UnitRandomimageWidget extends ContentWidget
{
    public function init()
    {
        parent::init();
        $this->params['image'] = $this->params['content']->images[rand(0,count($this->params['content']->images)-1)];
    }
}