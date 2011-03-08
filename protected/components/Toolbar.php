<?php

class Toolbar extends CWidget
{
    public $panelBackgroundColor = 'white';
    // Цвет фона кнопок
    public $buttonBackgroundColor = 'white';
    // Цвет рамки панелей
    public $panelBorderColor = 'lightblue';
    // Цвет рамки кнопок
    public $buttonBorderColor = 'lightblue';
    // Прозрачность
    public $opacity = 0.95;
    // Радиус скругления по углам
    public $borderRadius = 7;
    // Набор иконок (набор, размер, цвет)
    public $iconSet = 'fatcow';
    // Размер иконок
    public $iconSize = '32x32';
    // Расположение
    public $location = array(
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
    );
    // Размещение кнопок (вертикально или горизонтально)
    public $vertical = true;
    // Количество рядов или столбцов
    public $rows = 1;
    // Уровень слоя
    public $zIndex = 900;
    // Функции отображения и скрытия
    public $functionShow = "show()";
    public $functionHide = "hide()";
    // Обработка двойного клика
    public $dblclick = null;

    public static $iconsets = null;

    public $buttons = array();

    public function init()
    {
        if (!self::$iconsets)
            self::$iconsets = include(Yii::getPathOfAlias('application.config').'/iconsets.php');
    }

    public function run()
    {
        $config = get_object_vars($this);
        $config['id'] = $this->id;
        list($width, $height) = explode('x', $config['iconSize']);
        $id = $config['id'] ? $config['id'] : 'toolbar_'.sprintf('%x',crc32(serialize(array_keys($config['buttons']))));

        $this->render('toolbar', array(
            'config' => $config,
            'width' => $width,
            'height' => $height,
            'id' => $id
        ));
    }

    public static function getIconUrlByAlias($alias, $template='', $iconSet='', $iconSize='')
    {
        if (!self::$iconsets)
            self::$iconsets = include(Yii::getPathOfAlias('application.config').'/iconsets.php');

        if ($template=='')
            $template = 'url';
        if ($iconSet=='')
            $iconSet = self::$iconSet;
        if ($iconSize=='')
            $iconSize = self::$iconSize;

        // Если указанного набора нету в библиотеке, выбираем первый набор из списка
        if (!isset(self::$iconsets[$iconSet])) {
            foreach (self::$iconsets as $k => $v) {
                $iconSet = $k;
                break;
            }
        }
        // Если нету нужного шаблона для поиска картинки, то завершаем с неудачей
        if (!isset(self::$iconsets[$iconSet]['template'][$template]))
            return false;

        // Если в наборе нету подходящего размера, находим ближайший
        if (!in_array($iconSize, self::$iconsets[$iconSet]['sizes']))
        {
            list($w, $h) = explode('x', $iconSize);
            $iconSize = self::$iconsets[$iconSet]['sizes'][0];
            foreach (self::$iconsets[$iconSet]['sizes'] as $v) {
                list($_w, $_h) = explode('x', $v);
                if ($_w <= $w || $_h <= $h)
                    $iconSize = $v;
            }
        }

        $_alias = isset(self::$iconsets[$iconSet]['aliases'][$alias])
                ? self::$iconsets[$iconSet]['aliases'][$alias]
                : $alias;

        $filename = strtr(self::$iconsets[$iconSet]['template'][$template], array(
            '{alias}' => $_alias,
            '{size}' => $iconSize
        ));

        // Если файла нету, то ищем похожий
        if (!is_file(YiiBase::getPathOfAlias('webroot') . $filename))
        {
            $dir = dirname(YiiBase::getPathOfAlias('webroot') . $filename);
            $filename = strtr(self::$iconsets[$iconSet]['template'][$template], array(
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
