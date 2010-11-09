<?php

class UnitVideo extends Content
{
	const NAME = "Видео";
	const ICON = '/images/icons/iconic/green/play_12x16.png';
    const HIDDEN = false;

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'units_video';
	}

	public function rules()
	{
		return array(
			array('unit_id', 'required'),
			array('unit_id, width, height', 'numerical', 'integerOnly'=>true),
			array('video', 'length', 'max'=>255),
            array('html', 'length', 'max'=>3000),
            array('show_link', 'boolean')
		);
	}

	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'unit_id' => 'Unit',
			'video' => 'Ссылка на видео',
			'width' => 'Ширина',
			'height' => 'Высота',
            'html' => 'HTML-код',
            'show_link' => 'Отображать ссылку',
		);
	}

	public static function form()
	{
		$className = __CLASS__;
		$slideWidth = <<<EOD
js:function(event,ui) {
	$('#{$className}_width').val(ui.value);
	{$className}_makesize(ui.value, false, ui.handle);
}
EOD;
		$changeWidth = <<<EOD
js:function(event,ui) {
	{$className}_makesize(ui.value, false, ui.handle);
}
EOD;
		$slideHeight = <<<EOD
js:function(event,ui) {
	$('#{$className}_height').val(ui.value);
	{$className}_makesize(ui.value, true, ui.handle);
}
EOD;
		$changeHeight = <<<EOD
js:function(event,ui) {
	{$className}_makesize(ui.value, true, ui.handle);
}
EOD;
    
        return array(
			'elements'=>array(
                Form::tab('Видео'),
				'video'=>array(
					'type'=>'Link',
					'size'=>40,
                    'showPageSelectButton'=>false,
				),
                'show_link'=>array(
                    'type'=>'checkbox',
                ),
                self::renderFile($className, 'containersize', array(
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
                )),
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
                Form::tab('HTML-код'),
                'Если ссылка вашего видеохостинга не распознается, используйте вариант с html-кодом.',
                'html'=>array(
                    'type'=>'textarea',
                        'rows'=>6,
                        'cols'=>60
                )
			),
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

    public static function getHtmlByUrl($url, $width=0, $height=0, $title='')
    {
/*
 * TODO:
 * - vkontakte.ru
 * - video.yandex.ru
 * - bbc ?
 * - cnn ?
 * -
 */

        $vs = array(
            'youtube.com' => array(
                'pattern' => "|youtube\.com([^0-9\?\#\=]*)(\?v[=/])?(#p/u/)?([0-9]*/)?([a-zA-Z0-9]*)|msi",
                'match' => 5,
                'width' => 480,
                'height' => 385,
                'view' => 'youtube',
            ),
            'vimeo.com' => array(
                'pattern' => "|vimeo\.com(/video)?/([0-9]*)|msi",
                'match' => 2,
                'width' => 480,
                'height' => 270,
                'view' => 'vimeo',
            ),
            'rutube.ru' => array(
                'pattern' => "|rutube\.ru.*v=([0-9a-f]*)|msi",
                'match' => 1,
                'width' => 480,
                'height' => 360,
                'view' => 'rutube',
            ),
            'godtube.com' => array(
                'pattern' => "|godtube\.com.*v[=/]([a-zA-Z0-9]*)|msi",
                'match' => 1,
                'width' => 480,
                'height' => 385,
                'view' => 'godtube',
            ),
            'tangle.com' => array(
                'pattern' => "|tangle\.com.*viewkey=([0-9a-f]*)|msi",
                'match' => 1,
                'width' => 480,
                'height' => 385,
                'view' => 'tangle',
            ),
            'video.google.com' => array(
                'pattern' => "|google\.com.*docid=([\-0-9]*)|msi",
                'match' => 1,
                'width' => 480,
                'height' => 385,
                'view' => 'google',
            ),
            'myspace.com' => array(
                'pattern' => "|myspace\.com.*videoid=([0-9]*)|msi",
                'match' => 1,
                'width' => 425,
                'height' => 360,
                'view' => 'myspace',
            ),
            'dailymotion.com' => array(
                'pattern' => "|dailymotion\.com/video/([^?]*)|msi",
                'match' => 1,
                'width' => 480,
                'height' => 385,
                'view' => 'dailymotion',
            ),
            'truegod.tv' => array(
                'pattern' => "|truegod\.tv.*viewkey=([0-9a-f]*)|msi",
                'match' => 1,
                'width' => 480,
                'height' => 385,
                'view' => 'truegod',
            ),
            'smotri.com' => array(
                'pattern' => "|smotri\.com.*id=([0-9a-z]*)|msi",
                'match' => 1,
                'width' => 640,
                'height' => 360,
                'view' => 'smotri',
            ),
            '1tv.ru' => array(
                'pattern' => "|1tv\.ru[^0-9]*([0-9]*)|msi",
                'match' => 1,
                'width' => 460,
                'height' => 353,
                'view' => '1tv',
            ),
            'video.bigmir.net' => array(
                'pattern' => "|bigmir\.net[^0-9]*([0-9]*)|msi",
                'match' => 1,
                'width' => 608,
                'height' => 342,
                'view' => 'bigmir',
            ),
            'video.online.ua' => array(
                'pattern' => "|online\.ua[^0-9]*([0-9]*)|msi",
                'match' => 1,
                'width' => 640,
                'height' => 400,
                'view' => 'online_ua',
            ),
            'vision.rambler.ru' => array(
                'pattern' => "|rambler\.ru/users/(.*)|msi",
                'match' => 1,
                'width' => 480,
                'height' => 291,
                'view' => 'rambler',
            ),
            'video.utro.ua' => array(
                'pattern' => "|utro\.ua.*id=([0-9a-z]*)|msi",
                'match' => 1,
                'width' => 640,
                'height' => 360,
                'view' => 'utro',
            ),
            $_SERVER['HTTP_HOST'] => array(
                'pattern' => "|(.*)|msi",
                'match' => 1,
                'width' => 492,
                'height' => 300,
                'view' => 'jwplayer',
            ),
            '.flv' => array(
                'pattern' => "|(.*)|msi",
                'match' => 1,
                'width' => 492,
                'height' => 300,
                'view' => 'jwplayer',
            ),
            '.mp4' => array(
                'pattern' => "|(.*)|msi",
                'match' => 1,
                'width' => 492,
                'height' => 300,
                'view' => 'jwplayer',
            ),
        );

        foreach ($vs as $site=>$arr) {
            if (strpos($url, $site) !== false) {
                preg_match($arr['pattern'], $url, $matches);
                $id = $matches[$arr['match']];
                $w = $width ? $width : $arr['width'];
                $h = $height ? $height : $arr['height'];
                return Content::renderFile('UnitVideo', $arr['view'], array(
                    'id' => $id,
                    'width' => $w,
                    'height' => $h,
                    'title' => $title,
                ));
            }
        }
        return false;
    }

}