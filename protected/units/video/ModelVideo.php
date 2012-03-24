<?php

class ModelVideo extends ContentModel
{
    public function icon()
    {
        return '/images/icons/fatcow/16x16/movies.png';
    }
    
    public function modelName($language=null)
    {
        return Yii::t('UnitVideo.main', 'Video', array(), null, $language);
    }

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'widgets_video';
	}

	public function rules()
	{
		return array(
            array('widget_id', 'required', 'on'=>'edit'),
			array('widget_id, width, height', 'numerical', 'integerOnly'=>true),
			array('video', 'length', 'max'=>255, 'encoding'=>'UTF-8'),
            array('html', 'length', 'max'=>3000, 'encoding'=>'UTF-8'),
            array('show_link', 'boolean')
		);
	}

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
//			'widget_id' => 'Widget',
			'video' => Yii::t('UnitVideo.main', 'Video link'),
			'width' => Yii::t('UnitVideo.main', 'Width'),
			'height' => Yii::t('UnitVideo.main', 'Height'),
            'html' => Yii::t('UnitVideo.main', 'HTML'),
            'show_link' => Yii::t('UnitVideo.main', 'Display link'),
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
                Form::tab(Yii::t('UnitVideo.main', 'Video')),
				'video'=>array(
					'type'=>'Link',
					'size'=>40,
                    'showPageSelectButton'=>false,
				),
                'show_link'=>array(
                    'type'=>'checkbox',
                ),
                Yii::app()->controller->renderPartial($cfg['UnitVideo'].'.video.assets.containersize', array(
                    'attribute' => 'video',
                    'width' => 'width',
                    'height' => 'height',
                    'selector' => 'object, embed, iframe',
                    'sizes' => array(
                        '425x344' => '425 × 344',
                        '480x385' => '480 × 385',
                        '640x505' => '640 × 505',
                        '960x745' => '960 × 745',
                    ),
                    'className' => $className,
                ), true),
				'width'=>array(
					'type'=>'Slider',
					'event'=>'none',
					'options'=>array(
						'min' => 1,
						'max' => 1000,
						'step' => 1,
						'slide' => $slideWidth,
						'change' => $changeWidth
					)
				),
				'height'=>array(
					'type'=>'Slider',
					'event'=>'none',
					'options'=>array(
						'min' => 1,
						'max' => 1000,
						'step' => 1,
						'slide' => $slideHeight,
						'change' => $changeHeight
					)
				),
                Form::tab(Yii::t('UnitVideo.main', 'HTML')),                
                'html'=>array(
                    'hint'=>Yii::t('UnitVideo.main', 'If the link to your video is not recognized, use the version with the html-code.'),
                    'type'=>'textarea',
                        'rows'=>6,
                        'cols'=>60
                )
			),
		);
	}

    public function scheme()
    {
        return array(
            'widget_id' => 'integer unsigned',
            'video' => 'string',
            'width' => 'integer unsigned',
            'height' => 'integer unsigned',
            'html' => 'text',
            'show_link' => 'boolean',
        );
    }

    public static function defaultObject()
	{
		$obj = new self;
		$obj->video = 'url';
		$obj->width = 100;
		$obj->height = 100;
		return $obj;
	}

    public function resizableObjects()
    {
        return array(
            'object, embed, iframe' => array(
                'attributes' => array('width', 'height'),
                'aspectRatio' => true,
            ),
        );
    }
}

