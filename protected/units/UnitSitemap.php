<?php

class UnitSitemap extends Content
{
	const NAME = "Карта сайта";
	const ICON = '/images/icons/iconic/green/read_more_16x16.png';
    const HIDDEN = true;

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'units_sitemap';
	}

	public function rules()
	{
		return array(
			array('unit_id', 'required'),
			array('unit_id, length, recursive, page_id, per_page', 'numerical', 'integerOnly'=>true),
            array('show_title', 'boolean'),
		);
	}

	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'unit_id' => 'Unit',
			'length' => 'Длина описания',
            'recursive' => 'Количество уровней',
            'page_id' => 'Страница',
            'show_title' => 'Отображать заголовок',
			'per_page' => 'Объектов на одну страницу',
		);
	}

	public static function form()
	{
		return array(
			'elements'=>array(
                'show_title'=>array(
                    'type'=>'checkbox',
                ),
				'length'=>array(
					'type'=>'Slider',
					'options'=>array(
						'min' => 1,
						'max' => 2000,
						'step' => 50,
					)
				),
				'recursive'=>array(
					'type'=>'Slider',
					'options'=>array(
						'min' => 0,
						'max' => 10,
					)
				),
                'Если выбран 0, то отображаются соседние страницы',
				'per_page'=>array(
					'type'=>'Slider',
					'options'=>array(
						'min' => 0,
						'max' => 25,
					)
				),
                'Если выбран 0, то согласно общих настроек сайта.',
				'page_id'=>array(
					'type'=>'PageSelect',
                    'excludeCurrent'=>false,
				),
			),
		);
	}

    public static function renderPagelist($pages,$recursive=0,$length=0,$ul_class='',$li_class='',$a_class='',$p_class='') {
        $ul_class = $ul_class ? ' class="'.$ul_class.'"' : '';
        $li_class = $li_class ? ' class="'.$li_class.'"' : '';
        $a_class = $a_class ? ' class="'.$a_class.'"' : '';
        $p_class = $p_class ? ' class="'.$p_class.'"' : '';
        $output = '';
        if (count($pages) > 0)  {
            $output .= '<ul'.$ul_class.'>';
            foreach ($pages as $page) {
                $output .= '<li'.$li_class.'><a'.$a_class.' href="'.Yii::app()->controller->createAbsoluteUrl('page/view', array('id'=>$page->id)).'">'.$page->title.'</a>';

                if ($page->description && $length) {
                    $output .= '<p'.$p_class.'>' . nl2br(substr(strip_tags($page->description),0,$length)) . '</p>';
                }

                if ($recursive>0)
                        $output .= self::renderPagelist($page->children, $recursive-1);

                $output .= '</li>';
            }
            $output .= '</ul>';
        }
        return $output;
    }


}