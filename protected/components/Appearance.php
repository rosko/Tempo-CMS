<?php

/**
 * Класс (компонент приложения) предназначен для настройки и отображения разных
 * графических элементов (в основном для элементов управления):
 * - всплывающие панели
 * - панели управления
 * TODO: перевести сюда настройку и отображение:
 * - PageUnitPanel
 * - Toolbar
 * TODO: перевести сюда настройки темы оформления для jui
 */

class Appearance extends CApplicationComponent
{
    protected $default = array(
        // Цвет фона панелей
        'panelBackgroundColor' => 'white',
        // Цвет фона кнопок
        'buttonBackgroundColor' => 'white',
        // Цвет рамки панелей
        'panelBorderColor' => 'lightblue',
        // Цвет рамки кнопок
        'buttonBorderColor' => 'lightblue',
        // Прозрачность
        'opacity' => 0.95,
        // Радиус скругления по углам
        'borderRadius' => 7,
        // Набор иконок (набор, размер, цвет)
        'iconSet' => '',
        // Размер иконок
        'iconSize' => '24x24',
        // Расположение
        'location' => array(
            'selector' => '.cms-pageunit',
            'position' => array(
                'outter',  // inner, outter, absolute
                'left',  // left, right, top, bottom
                'top', // left, right, top, bottom
            ),
            'show' => 'always', // always, hover, click
            // Можно ли перемещать мышкой
            'draggable' => true,
            // Сохранять ли положение
            'savePosition' => true,
        ),
        // Размещение кнопок (вертикально или горизонтально)
        'vertical' => true,
        // Количество рядов или столбцов
        'rows' => 1,
        // Уровень слоя
        'zIndex' => 1000,
        // Функции отображения и скрытия
        'functionShow' => "show()",
        'functionHide' => "hide()",
    );

    public $iconsets = array();
    public $options = array();
    

    public function init()
    {
        foreach ($this->options as $k => $v)
        {
            $this->default[$k] = $v;
        }
    }

    public function toolbar($config)
    {
        foreach (array_keys($this->default) as $s)
        {
            if (!isset($config[$s]))
                $config[$s] = $this->default[$s];
        }
        list($width, $height) = explode('x', $config['iconSize']);
        $id = $config['id'] ? $config['id'] : 'toolbar_'.sprintf('%x',crc32(serialize(array_keys($config))));

        return Yii::app()->controller->renderPartial('application.components.views.toolbar', array(
            'config' => $config,
            'width' => $width,
            'height' => $height,
            'id' => $id
        ), true);
    }

    public function getIconUrlByAlias($alias, $template='', $iconSet='', $iconSize='')
    {
        if ($template=='')
            $template = 'url';
        if ($iconSet=='')
            $iconSet = $this->default['iconSet'];
        if ($iconSize=='')
            $iconSize = $this->default['iconSize'];

        // Если указанного набора нету в библиотеке, выбираем первый набор из списка
        if (isset($this->iconsets[$iconSet])) {
            foreach ($this->iconsets as $k => $v) {
                $iconSet = $k;
                break;
            }
        }
        // Если нету нужного шаблона для поиска картинки, то завершаем с неудачей
        if (!isset($this->iconsets[$iconSet]['template'][$template]))
            return false;

        // Если в наборе нету подходящего размера, находим ближайший 
        if (!in_array($iconSize, $this->iconsets[$iconSet]['sizes']))
        {
            list($w, $h) = explode('x', $iconSize);
            foreach ($this->iconsets[$iconSet]['sizes'] as $v) {
                list($_w, $_h) = explode('x', $v);
                if ($_w <= $w || $_h <= $h)
                    $iconSize = $v;
            }
        }

        $_alias = isset($this->iconsets[$iconSet]['aliases'][$alias])
                ? $this->iconsets[$iconSet]['aliases'][$alias]
                : $alias;

        $filename = strtr($this->iconsets[$iconSet]['template'][$template], array(
            '{alias}' => $_alias,
            '{size}' => $iconSize
        ));
        
        // Если файла нету, то ищем похожий
        if (!is_file(YiiBase::getPathOfAlias('webroot') . $filename))
        {
            $dir = dirname(YiiBase::getPathOfAlias('webroot') . $filename);
            $filename = strtr($this->iconsets[$iconSet]['template'][$template], array(
                '{alias}' => $_alias
            ));
            $bdir_len = strlen(YiiBase::getPathOfAlias('webroot'));
            $cmp = substr($filename,0,strpos($filename, '{size}'));
            $cmp_len = strlen($cmp);
            if (!is_dir($dir)) return false;
            $files = CFileHelper::findFiles($dir, array(
                'fileTypes' => array('png', 'gif', 'jpg'),
            ));
            $found = array();
            foreach ($files as $file)
            {
                $file = substr($file,$bdir_len);
                if (substr($file,0,$cmp_len)==$cmp) {
                    $found[] = $file;
                }
            }
            list($w, $h) = explode('x', $iconSize);
            natsort($found);
            foreach ($found as $file)
                if (preg_match('/^([0-9]*)x([0-9]*)/msi', substr($file,$cmp_len), $matches)) 
                    if ($w >= $matches[1] || $h >= $matches[2]) 
                        $filename = $file;
            return $filename;

        } else return $filename;
    }

}
