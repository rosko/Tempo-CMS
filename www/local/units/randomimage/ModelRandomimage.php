<?php

class ModelRandomimage extends ContentModel
{
    public function icon()
    {
        return '/images/icons/fatcow/16x16/image.png';
    }
    
    public function modelName($language=null)
    {
        return Yii::t('UnitRandomimage.main', 'Random image', array(), null, $language);
    }

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'widgets_randomimage';
	}

	public function rules()
	{
		return array(
            array('widget_id', 'required', 'on'=>'edit'),
			array('width, height', 'required'),
			array('widget_id, width, height', 'numerical', 'integerOnly'=>true),
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
        $className = __CLASS__;
        $slideWidth = 'js:function(event,ui)'.<<<JS
 {
	$('#{$className}_width').val(ui.value);
	{$className}_makesize(ui.value, false, ui.handle);
}
JS;
        $changeWidth = 'js:function(event,ui)'.<<<JS
 {
	{$className}_makesize(ui.value, false, ui.handle);
}
JS;
        $slideHeight = 'js:function(event,ui)'.<<<JS
 {
	$('#{$className}_height').val(ui.value);
	{$className}_makesize(ui.value, true, ui.handle);
}
JS;
        $changeHeight = 'js:function(event,ui)'.<<<JS
 {
	{$className}_makesize(ui.value, true, ui.handle);
}
JS;
        $cfg = ContentUnit::loadConfig();

        return array(
			'elements'=>array(
                Form::tab(Yii::t('UnitRandomimage.main', 'Images')),
                'images' => array(
                    'type' => 'FileManager',
                    'width' => 900,
                    'height' => 350,
                    'multiple' => true,
                    'options' => array(
                        'onlyMimes' => array('image/jpeg', 'image/gif', 'image/png'),
                    ),
                    'element' => array(
                        array(
                            'name' => 'caption',
                            'type' => 'text',
                            'size' => 40,
                            'label' => Yii::t('UnitRandomimage.main', 'Caption'),
                            'i18n' => true,
                        ),
                    ),
                ),
                Form::tab(Yii::t('UnitRandomimage.main', 'Size & link')),
                Yii::app()->controller->renderPartial($cfg['UnitImage'].'.image.assets.imagesize', compact('className'), true),
                'width'=>array(
					'type'=>'Slider',
					'event'=>'none',
					'options'=>array(
						'min' => 1,
						'max' => 2000,
						'step' => 1,
                        'slide' => $slideWidth,
                        'change' => $changeWidth,
                    )
				),
				'height'=>array(
					'type'=>'Slider',
					'event'=>'none',
					'options'=>array(
						'min' => 1,
						'max' => 2000,
						'step' => 1,
                        'slide' => $slideHeight,
                        'change' => $changeHeight,
                    )
				),
				'url'=>array(
					'type'=>'text',
					'size'=>40,
					//'showUploadButton'=>false
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
            'widget_id' => 'integer unsigned',
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

