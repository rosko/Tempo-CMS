<?php

class FilesEditorController extends Controller
{
	public $defaultAction = 'form';

	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
	}

	public function accessRules()
	{
		return array(
			array('allow',
				'actions'=>array('form', 'save', 'load', 'create', 'delete'
                ),
				'users'=>array('admin'),
			),
			array('deny',
				'users'=>array('*'),
			),
		);
	}

	// Отображает страницу
	public function actionForm($type, $name, $default='')
	{
        $files = array();
        $def = 0;
        $suggestions = array(
            ''=>'',
            '{dateformat pattern="d MMMM yyyy" time=$content.date}'=> 'Функция для отображения времени',
            '{link text="Ссылка" url="page/view?id=1"}' => 'Функция для отображения ссылки, например на страницу с ID=1',
        );
        if ($type == 'templates') {
            if (class_exists($name)) {
                $suggestions['{registercss file="file.css"}'] = 'Функция подключения CSS-файла, который размещен в папке ресурсов files текущего блока';
                $suggestions['{registerjs file="file.js"}'] = 'Функция подключения яваскрипт-файла, который размещен в папке ресурсов files текущего блока';

                if((Yii::app()->getViewRenderer())!==null)
                    $extension=Yii::app()->getViewRenderer()->fileExtension;
                else
                    $extension='.php';

                $data = $name::getTemplates($name, false);
                $files[] = array(
                    'name' => '',
                    'title' => '«обычный»',
                    'writable' => false,
                );
                foreach ($data as $file)
                {
                    $title = basename($file, $extension);
                    $files[] = array(
                        'name' => $title,
                        'title' => $title,
                        'writable' => is_writable($file),
                    );
                    if ($title == $default) {
                        $def = count($files)-1;
                    }
                }

                $title = 'Шаблоны блоков &laquo;'.$name::NAME.'&raquo;';

                // Формируем подсказки:
                $suggestions['{$editMode}'] = 'Признак режима редактирования';
                if (method_exists($name, 'templateVars'))
                {
                    $vars = $name::templateVars();
                    foreach ($vars as $k => $v) {
                        $suggestions[$k] = 'Блок :: ' . $v;
                    }
                }

                $objs = array(
                    'content'=>$name,
                    'unit'=>'Unit',
                    'pageunit'=>'PageUnit',
                    'page'=>'Page',
                );
                $names = array(
                    'unit'=>'Блок',
                    'content'=>'Блок',
                    'page'=>'Страница отображения',
                    'pageunit'=>'Размещение блока',
                );
                foreach ($objs as $param => $obj) {
                    $o= new $obj;
                    if (method_exists($o, 'attributeLabels') && method_exists($o, 'getAttributes')) {
                        $labels = $o->attributeLabels();
                        $attrs = $o->getAttributes();
                        foreach ($attrs as $attr => $value) {
                            if (isset($labels[$attr])) {
                                $k = '{$'.$param.'.'.$attr.'}';
                                $suggestions[$k] = $names[$param] . ' :: ' . $labels[$attr];
                            }
                        }
                    }
                }
                $setts = $name::settings($name);
                foreach ($setts as $k => $v) {
                    $var = '{$settings.local.'.$k.'}';
                    $suggestions[$var] = 'Настройки для блоков «'.$name::NAME.'» :: ' . $v['label'];
                }
            }
        } else {

        }
        $setts = SiteSettingsForm::attributeLabels();
        foreach ($setts as $k => $v) {
            $var = '{$settings.global.'.$k.'}';
            $suggestions[$var] = 'Глобальные настройки :: ' . $v;
        }
        $suggestions['{$TIME}'] = 'Затраченное время (в секундах)';
        $suggestions['{$MEMORY}'] = 'Использованная память (в мегабайтах)';
        if (!empty($files)) {
            $id = 'FilesEditor_'.sprintf('%x',crc32(serialize($files).$type.$name));
            $this->render('form', array(
                'id' => $id,
                'files' => $files,
                'type' => $type,
                'name' => $name,
                'title' => $title,
                'default' => $def,
                'suggestions' => $suggestions,
            ));
        }
    }

    protected function getFilenameByParams($type, $name, $file='')
    {
        $filename = '';
        if ($type == 'templates')
        {
            if (class_exists($name)) {
                if((Yii::app()->getViewRenderer())!==null)
                    $extension=Yii::app()->getViewRenderer()->fileExtension;
                else
                    $extension='.php';

                $dirs = $name::getTemplateDirAliases($name);
                foreach ($dirs as $s) {
                    $filename = $file ? Yii::getPathOfAlias($s).'/'.basename($file).$extension
                                      : Yii::getPathOfAlias($s).'/'.$name.$extension;
                    if (is_file($filename)) {
                        return $filename;
                    }
                }
                if (!is_file($filename)) return false;
            }
        }
        return $filename;
    }

    public function actionLoad($type, $name, $file)
    {
        $filename = $this->getFilenameByParams($type, $name, $file);
        if ($filename !== false)
            echo file_get_contents($filename);
    }

    public function actionSave($type, $name)
    {
        $filename = $this->getFilenameByParams($type, $name, $_REQUEST['file']);
        if (is_file($filename) && is_writable($filename))
            echo file_put_contents($filename, $_REQUEST['content'])!==false;
    }

    public function actionCreate($type, $name, $file)
    {
        $systemFile = $this->getFilenameByParams($type, $name);
        $content = is_file($systemFile) ? file_get_contents($systemFile) : '';

        if ($file != '') {
            $filename = '';
            if((Yii::app()->getViewRenderer())!==null)
                $extension=Yii::app()->getViewRenderer()->fileExtension;
            else
                $extension='.php';

            if ($type == 'templates') {
                if (class_exists($name))
                    $filename = Yii::getPathOfAlias('webroot.templates.'.$name).DIRECTORY_SEPARATOR.$file.$extension;
            }
            if ($filename) {
                if (!is_dir(dirname($filename))) {
                    mkdir(dirname($filename), 0777, true);
                }
                if (!is_file($filename) && is_writable(dirname($filename))) {
                    echo file_put_contents($filename, $content)!==false;
                }
            }
        }
    }

    public function actionDelete($type, $name, $file)
    {
        if ($file != '') {
            $filename = $this->getFilenameByParams($type, $name, $file);
            if (is_file($filename) && is_writeable($filename) && is_writeable(dirname($filename))) {
                echo unlink($filename);
            } 
        } 
    }


}
?>