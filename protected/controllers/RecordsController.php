<?php

class RecordsController extends Controller
{
	public $defaultAction = 'view';

	public function filters()
	{
		return array(
			'accessControl', 
		);
	}

	public function accessRules()
	{
		return array(
			array('allow',
				'actions'=>array('create', 'delete', 'getUrl'
                ),
				'users'=>array('@'),
			),
			array('deny',
				'users'=>array('*'),
			),
		);
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
            if ($_REQUEST['foreign_attribute'] && $_REQUEST['sectionId'] && $model->hasAttribute($_REQUEST['foreign_attribute']))
            {
                $model->{$_REQUEST['foreign_attribute']} = intval($_REQUEST['sectionId']);
            }
            $model->save(false);
            echo CJavaScript::jsonEncode(array(
                'id'=>$model->id,
            ));
        } else
            echo '0';
    }

    public function actionDelete($className, $id)
    {
        $ids =  is_array($id) ? $id : array($id);
        $ret = true;
        foreach ($ids as $id) {
            $model = call_user_func(array($className, 'model'))->findByPk($id);
            $ret = $model->delete() && $ret;
        }
        echo (int)$ret;
    }

    public function actionGetUrl()
    {
        if ($_REQUEST['id'] && $_REQUEST['className']) {
            $widgetClass = $_REQUEST['className'];
            $content = call_user_func(array($widgetClass, 'model'))->findByPk($_REQUEST['id']);
            if ($content->widget_id) {
                $widget = $content->widget;
                echo $widget->getWidgetUrl();
            }
            
        }
    }

}