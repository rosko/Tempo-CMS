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
				'actions'=>array('create', 'delete', 'getUrl', 'fields', 'list', 'search', 'massUpdate',
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

    public function actionDelete($className)
    {
        $id = Yii::app()->request->getQuery('id');
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

    public function actionFields($id, $name)
    {
        $config = @unserialize(base64_decode(Yii::app()->request->getPost('config')));
        $value = @unserialize(base64_decode(Yii::app()->request->getPost('value')));
        if (is_array($config)) {
            $this->widget('Fields', array(
                'id' => $id,
                'name' => $name,
                'config' => $config,
                'value' => $value,
            ));
        } else {
            throw new CHttpException(500,Yii::t('cms', 'The requested page does not exist.'));
        }
    }

    public function actionList($className)
    {
        $title = call_user_func(array($className, 'modelName'));
        $this->render('list', array(
                'className' => $className,
                'title' => $title,
            )
        );
    }

    public function actionSearch($className, $fieldName, $searchValue, $page=1)
    {
        $limit = Yii::app()->settings->getValue('defaultsPerPage');
        $page = intval($page);
        $object = new $className;

        $selectFields = $object->searchAttributes();
        if (method_exists($object, 'localizedAttributes')) {
            $selectFields = $object->localizedAttributes($selectFields);
        }

        $whereSql = array();

        $fieldNames = explode(',', $fieldName);

        foreach ($fieldNames as $fieldName) {

            $fieldName = trim($fieldName);
            $oldFieldName = $fieldName;

            if (!in_array($fieldName, $object->searchAttributes())) return false;
            if (method_exists($object, 'i18n') && in_array($fieldName, $object->i18n())) {
                $fieldName = Yii::app()->language . '_' . $fieldName;
            }

            if ($object->hasAttribute($fieldName)) {
                $selectFields[] = $fieldName.'` as `'.$oldFieldName;
                $whereSql[] = 'LOWER(`'.$fieldName.'`) LIKE LOWER(:value)';

            }

        }

        $fields = '`' . implode('`, `', $selectFields) . '`';
        $sql = 'SELECT `id`, '.$fields.' FROM `' . $object->tableName() . '`
            WHERE ' . implode(' OR ', $whereSql) . '
            LIMIT :start, :limit';
        $params = array(
            ':value' => $searchValue.'%',
            ':start' => $limit * ($page - 1),
            ':limit' => intval($limit+1),
        );
        $results = Yii::app()->getDb()->createCommand($sql)->bindValues($params)->queryAll();

        $ret = array(
            'results' => $results,
            'more' => count($results) > $limit,
        );
        echo CJSON::encode($ret);
    }

    public function actionMassUpdate($className, $fieldName, $fieldValue)
    {
        $id = Yii::app()->request->getQuery('id');
        $ids =  is_array($id) ? $id : array($id);
        $ret = true;
        foreach ($ids as $id) {
            $model = call_user_func(array($className, 'model'))->findByPk($id);
            $model->{$fieldName} = $fieldValue;
            $ret = $model->save(false) && $ret;
        }
        echo (int)$ret;
    }

}