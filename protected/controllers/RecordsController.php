<?php

class RecordsController extends Controller
{
	public $defaultAction = 'view';

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
				'actions'=>array('create', 'view', 'delete', 'getUrl'


                ),
				'users'=>array('admin'),
			),
			array('deny',
				'users'=>array('*'),
			),
		);
	}

	// Отображает страницу
	public function actionView()
	{

    }

    public function actionCreate()
    {
        if ($_REQUEST['class_name'])
        {
            $className = $_REQUEST['class_name'];
			if (method_exists($className, 'defaultObject')) {
				$model = call_user_func(array($className, 'defaultObject'));
			} else {
				$model = new $className;
			}
            if ($_REQUEST['foreign_attribute'] && $_REQUEST['section_id'] && $model->hasAttribute($_REQUEST['foreign_attribute']))
            {
                $model->{$_REQUEST['foreign_attribute']} = intval($_REQUEST['section_id']);
            }
            $model->save(false);
            echo CJavaScript::jsonEncode(array(
                'id'=>$model->id,
            ));
        } else
            echo '0';
    }

    public function actionDelete()
    {
        if ($_REQUEST['id'] && $_REQUEST['class_name']) {
            $ids =  is_array($_REQUEST['id']) ? $_REQUEST['id'] : array($_REQUEST['id']);
            $className = $_REQUEST['class_name'];
            $ret = true;
            foreach ($ids as $id) {
                $model = call_user_func(array($className, 'model'))->findByPk($id);
                $ret = $model->delete() && $ret;
            }
            echo (int)$ret;
        } else
            echo '0';
    }

    public function actionGetUrl()
    {
        if ($_REQUEST['id'] && $_REQUEST['class_name']) {
            $unit_class = $_REQUEST['class_name'];
            $content = call_user_func(array($unit_class, 'model'))->findByPk($_REQUEST['id']);
            if ($content->unit_id) {
                $unit = $content->unit;
                echo $unit->getUnitUrl();
            }
            
        }
    }

}