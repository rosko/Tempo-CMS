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
        );
        if ($type == 'templates') {
            if (class_exists($name)) {
                if((Yii::app()->getViewRenderer())!==null)
                    $extension=Yii::app()->getViewRenderer()->fileExtension;
                else
                    $extension='.php';

                $data = $name::getTemplates($name, false);
                $systemFile = Yii::getPathOfAlias('application.units.views.').'/'.$name.$extension;
                $files[] = array(
                    'name' => '',
                    'title' => '«обычный»',
                    'writable' => is_writable($systemFile)
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
            $this->renderPartial('form', array(
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

                $filename = $file ? Yii::getPathOfAlias('webroot.templates.'.$name).'/'.basename($file).$extension
                                  : Yii::getPathOfAlias('application.units.views.').'/'.$name.$extension;
            }
        }
        return $filename;
    }

    public function actionLoad($type, $name, $file)
    {
        $filename = $this->getFilenameByParams($type, $name, $file);
        if (is_file($filename))
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
            $filename = $this->getFilenameByParams($type, $name, $file);
            if (!is_dir(dirname($filename))) {
                mkdir(dirname($filename), 0777, true);
            }
            if (!is_file($filename) && is_writable(dirname($filename))) {
                echo file_put_contents($filename, $content)!==false;
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