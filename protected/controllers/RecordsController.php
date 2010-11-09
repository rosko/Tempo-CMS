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
				'actions'=>array('view', 'getUrl'


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