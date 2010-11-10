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
				'actions'=>array('view', 'delete', 'getUrl'


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

    public function actionDelete()
    {
        if ($_REQUEST['id'] && $_REQUEST['class_name']) {
            $ids =  is_array($_REQUEST['id']) ? $_REQUEST['id'] : array($_REQUEST['id']);
            $className = $_REQUEST['class_name'];
            $ret = true;
            foreach ($ids as $id) {
                $model = $className::model()->findByPk($_REQUEST['id']);
                $ret = $ret && $model->delete();
            }
            echo (int)$ret;
        }
    }

    public function actionGetUrl()
    {
        if ($_REQUEST['id'] && $_REQUEST['class_name']) {
            $unit_class = $_REQUEST['class_name'];
            $content = $unit_class::model()->findByPk($_REQUEST['id']);
            if ($content->unit_id) {
                $unit = $content->unit;
                echo $unit->getUnitUrl();
            }
            
        }
    }

}
?>