<?php

class Toolbar extends CWidget
{
    public $cssClass = 'cms-menu';
    public $cssFile = null;
    public $iconSize = 'small'; // small, big

    // Расположение
    public $location = array(
        'selector' => '.cms-pagewidget',
        'position' => array(
            'outter',  // inner, outter, absolute
            'left',  // left, right, top, bottom, wide
            'top', // left, right, top, bottom, wide
        ),
        'show' => 'always', // always, hover, click
        // Можно ли перемещать мышкой
        'draggable' => true,
        // Сохранять ли положение
        'savePosition' => true,
    );
    // Показывать ли заголовки кнопок
    public $showTitles = false;
    // Показывать ли иконки кнопок
    public $showIcons = true;
    // Размещение кнопок (вертикально или горизонтально)
    public $vertical = true;
    // Количество рядов или столбцов
    public $rows = 1;
    // Функции отображения и скрытия
    public $functionShow = "show()";
    public $functionHide = "hide()";
    // Обработка двойного клика
    public $dblclick = null;

    public $buttons = array();

    public function init()
    {
        if (!$this->cssFile)
            $this->cssFile = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.assets.css')).'/toolbar.css';
    }

    public function run()
    {
        $config = get_object_vars($this);
        $config['id'] = $this->id;
        $id = $config['id'] ? $config['id'] : 'toolbar_'.sprintf('%x',crc32(serialize(array_keys($config['buttons']))));

        $this->render('toolbar', array(
            'config' => $config,
            'id' => $id
        ));
    }

    public function renderButtons($buttons, $vertical, $rows, $config, $id, &$js)
    {
        $output = '<div><ul>';
        if (!$rows) $rows = 1;
        if (is_array($buttons)) {
            $nl = $vertical ? $rows : ceil(count($buttons) / $rows);
            $i=0;
            foreach ($buttons as $name => $button) {
                if ($button==null) continue;
                $i++;
                $output .= $this->renderbutton($button, $name, $config, $id, $js);
                if ($nl == $i) {
                    $output .= '</ul><ul>';
                    $i=0;
                }
            }
        }
        $output .= '</ul></div>';
        return $output;
    }

    public function renderButton($button, $name, $config, $id, &$js)
    {
        $output = '';
        $buttonCssClass = 'cms-icon-'.$config['iconSize'].'-'.$button['icon'];

        if ($button['click']) $js .= "$('#{$id}_{$name}').click(".CJavaScript::encode($button['click']).");\n";


        $output .= '<li id="'.$id.'_'.$name.'_li" class="'.$button['cssClass'].'">';
        $output .= '<a id="'.$id.'_'.$name.'"
                title="'.$button['title'].'" href="#">';
        if (isset($button['checked'])) {
            $output .= '<input ';
            if ($button['checked']) $output .= ' checked="checked" ';
            $output .= 'type="checkbox" id="'.$id.'_'.$name.'_checkbox" /><label for="'.$id.'_'.$name.'_checkbox">';
        }
        if ($config['showIcons']) {
            $output .= '<span class="'.$buttonCssClass.'"></span>';
        }

        if ($config['showTitles'])
            $output .= $button['title'];
        if (isset($button['checked'])) {
            $output .= '</label>';
        }
        $output .= '</a>';
        $output .= '</li>';

        return $output;

    }

}
