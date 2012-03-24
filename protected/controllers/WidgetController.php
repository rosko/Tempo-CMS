<?php
/**
 * WidgetController
 *
 * @author Alexey Volkov <a@insvit.com>
 * @link http://www.insvit.com/
 * @copyright Copyright &copy; 2010-2011 Alexey Volkov
 * 
 */

/**
 * WidgetController - это класс контроллера, который отвечает за обработку ajax-запросов
 * для управления блоками
 */
class WidgetController extends Controller
{
	public function filters()
	{
		return array('accessControl');
	}

	public function accessRules()
	{
		return array(
			array('allow',
				'actions'=>array('check', 'delete', 'deleteDialog', 'edit',
                    'move', 'set', 'setDialog'),
				'users'=>array('@'),
			),
            array('allow',
                'actions'=>array('ajax', 'getPageWidgetsByWidgetId'),
                'users'=>array('*'),                
            ),
			array('deny',
				'users'=>array('*'),
			),
		);
	}

    /**
     * Обрабатывает запрос на редактирование блока
     *
     * @param int $pageWidgetId id редактируемого страничного блока
     * @param int $widgetId id редактируемого блока
     * @param int $pageId id страницы, на которой размещается блок
     * (используется, если предполагается создание нового блока)
     * @param int $prevPageWidgetId id блока, после которого размещается текущий блок
     * (используется, если предполагается создание нового блока)
     * @param string $area название области страницы где размещается блок
     * (используется, если предполагается создание нового блока)
     * (используется, если предполагается создание нового блока)
     * @param string $modelClass имя класса модели редактируемой записи
     * @param string $widgetClass имя класса виджета редактируемого блока
     * @param int $recordId id редактируемой записи
     * @param int $sectionId
     * @param string foreignAttribute
     * @param string $return тип возвращаемого ответа (json, html)
     */
    public function actionEdit($pageWidgetId=0, $widgetId=0, $pageId=0, $prevPageWidgetId=0, $area='main', $modelClass='', $widgetClass='', $recordId=0, $sectionId=0, $foreignAttribute='', $return='html')
    {
        $ret = true;
        // Обрабатываем входящие параметры и находим (или создаем) необходимые объекты
        $pageWidgetId = (int)$pageWidgetId;
        $widgetId = (int)$widgetId;
        $recordId = (int)$recordId;
        $pageId = (int)$pageId;
        // Если указан id страничного блока
        if ($pageWidgetId>0) {
            $pageWidget = PageWidget::model()->with('widget')->findByPk($pageWidgetId);
            if ($pageWidget) {
                $widget = $pageWidget->widget;
                $widgetClass = $widget->class;
                $content = $widget->content;
                $content->scenario = 'edit';
            }
        // Если указан id блока в общем
        } elseif ($widgetId>0) {
            $widget = Widget::model()->findByPk($widgetId);
            $widgetClass = $widget->class;
            if ($widget) {
                $content = $widget->content;
                $content->scenario = 'edit';
            }
        // Если указан класс и id модели
        } elseif ($recordId>0 && $modelClass) {
            $content = call_user_func(array($modelClass, 'model'))->findByPk($recordId);
            $content->scenario = 'edit';
        // Если указан только класс виджета
        } elseif ($widgetClass || $modelClass) {
            if ($widgetClass)
                $modelClass = call_user_func(array($widgetClass,'modelClassName'));
            if (method_exists($modelClass, 'defaultObject')) {
                $content = call_user_func(array($modelClass, 'defaultObject'));
            } else {
                $content = new $modelClass;
            }
            $content->scenario = 'add';
        } else {
            $ret = false;
        }
        if (!isset($widget) && $content && $content->hasAttribute('widget_id')) {
            if (!empty($content->widget_id)) {
                $widget =  $content->widget;
                $widgetClass = $widget->class;
            } elseif ($content->scenario == 'add') {
                $widget = new Widget;
            }
        }

        $modelClass = get_class($content);
        $langs = array_keys(I18nActiveRecord::getLangs(Yii::app()->language));
        // Если блок новый, заполняем его исходными данными
        if (isset($widget) && $widgetClass && $widget->isNewRecord) {
			$widget->class = $widgetClass;
			$widget->title = call_user_func(array($modelClass, 'modelName'));
            foreach ($langs as $lang) {
                $widget->{$lang.'_title'} = call_user_func(array($modelClass, 'modelName'), $lang);
            }
        }
        // Если указывается внешний ключ
        $sectionId = (int)$sectionId;
        if ($sectionId && $foreignAttribute && $content->hasAttribute($foreignAttribute)) {
            $content->{$foreignAttribute} = $sectionId;
        }

        // Делаем форму редактирования
        $id = 'WidgetEdit'.$modelClass;

        $widgetFormArray = call_user_func(array($modelClass, 'form'));
        $widgetFormArray['type'] = 'form';
        $widgetFormArray['id'] = $id;
        $showTitle = true;
        if (isset($widgetFormArray['title'])) {
            if ($widgetFormArray['title'] === false)
                $showTitle = false;
            unset($widgetFormArray['title']);
        }
        //  В окно редактирования свойств подключаем диалог для управления размещением
        //  todo: нужно исправить урл
//        if (isset($pageWidget)) {
//            $widgetFormArray['elements'][] = Form::tab('Размещение', '/?r=widget/setDialog&pageId='.$pageWidget->page_id.'&widgetId='.$pageWidget->widget_id.'&pageWidgetId='.$pageWidget->id);
//        }
		$formArray = array(
            'id' => $id,
            'activeForm' => Form::ajaxify($id),
            'action' => Yii::app()->request->getUrl(),
            'buttons'=>array(
				'save'=>array(
					'type'=>'submit',
					'label'=>Yii::t('cms', 'Save'),
					'title'=>Yii::t('cms', 'Save and close window'),
				),
				'apply'=>array(
					'type'=>'submit',
					'label'=>Yii::t('cms', 'Apply'),
					'title'=>Yii::t('cms', 'Save and continue editing'),
				),
			)
		);
        if ($content->scenario == 'add')
            $formArray['activeForm']['clientOptions']['afterValidate'] = 'js:function(f,d,h)'.<<<JS
{cmsAjaxSubmitForm(f,d,h, {
    onSuccess: function(html) {

        var params = $(html).find('form').attr('action').split('&');
        for (var i=0; i<params.length; i++)
        {
            var a = params[i].split('=');
            if (a[0] == 'pageWidgetId') {
                var pageWidgetId = a[1];
            }
            if (a[0] == 'widgetId') {
                var widgetId = a[1];
            }
        }
        var prevPageWidgetId = '{$prevPageWidgetId}';
        var areaName = '{$area}';
        var widgetClass = '{$widgetClass}';
        var pageId = '{$pageId}';
        if ($('#cms-pagewidget-'+pageWidgetId).length==0) {
            if (prevPageWidgetId != '0') {
                var prevPageWidget = $('#cms-pagewidget-'+prevPageWidgetId);
            } else {
                var prevPageWidget = $('#cms-area-'+areaName).find('.cms-empty-area-buttons').eq(0);
            }
            prevPageWidget.after('<div id="cms-pagewidget-'+pageWidgetId+'" class="cms-pagewidget cms-widget-'+widgetClass+'" rel="'+widgetClass+'" rev="'+widgetId+'" style="cursor:move;"></div>');
        }
        var pageWidget = $('#cms-pagewidget-'+pageWidgetId);
        if (pageWidget.length) {
            var origBg = pageWidget.css('backgroundColor');
            cmsReloadPageWidget(pageWidgetId, '.cms-pagewidget[rev='+widgetId+']', function() {
                cmsAreaEmptyCheck();
            });
            pageWidget.css('backgroundColor', '#FFFF00').animate({
                backgroundColor: origBg
            }, 2500);
            $.scrollTo('#cms-pagewidget-'+pageWidgetId, 'normal', {
                offset: -10
            });
        }


    }
});
}
JS;

        if (substr($widgetFormArray['elements'][0],0,2)!=Form::TAB_DELIMETER
                || substr($widgetFormArray['elements'][0],-2)!=Form::TAB_DELIMETER)
        {
            $formArray['title']='';
        }
        if (method_exists($modelClass, 'modelName')) {
            if (is_subclass_of($modelClass, 'Content')) {
                $caption = array(
                    'icon' => call_user_func(array($modelClass, 'icon')),
                    'label' => call_user_func(array($modelClass, 'modelName')),
                );
            } else {
                $caption = array(
                    'icon' => $content->icon(),
                    'label' => $content->modelName(),
                );
            }
        } else {
        }
        if (isset($widget)) {
            $formArray['elements']['widget'] = array(
                'type'=>'form',
                'id' => $id,
                'elements'=>array(
                    'title'=>array(
                        'type'=>'text',
                        'maxlength'=>255,
                        'size'=>60
                    ),
                )
            );
            if (Yii::app()->settings->getValue('showWidgetAppearance')) {
                $formArray['elements']['widget']['elements']['template'] = array(
                    'type'=>'TemplateSelect',
                    'className'=>$widgetClass,
                    'empty'=>Yii::t('cms', '«accordingly to general settings»'),
                );
                $widgetFormArray['elements'][] = Form::tab(Yii::t('cms', 'Appearance'));
            }
        }
        $formArray['elements']['content'] = $widgetFormArray;

		$form = new Form($formArray);
        if (isset($widget))
            $form['widget']->model = $widget;
		$form['content']->model = $content;

        // ajax-валидация
        if (isset($widget)) {
            $this->performAjaxValidation(array($widget, $content));
        } else
            $this->performAjaxValidation($content);

		// Проверка и сохранение
        if ($form->submitted('save') || $form->submitted('apply')) {
            if (isset($widget))
                $widget = $form['widget']->model;
			$content = $form['content']->model;
			if ($form->validate()) {
                if (isset($widget)) {
                    $isWidgetNew = $widget->isNewRecord;
            		if ($ret && $widget->save(false)) {
                        $formArray['action'] .= '&widgetId='.$widget->id;
            			// Если блок новый, размещаем его на текущей странице
                        if ($isWidgetNew) {
                            $prevPageWidgetId = (int)$prevPageWidgetId;
                            $order = $prevPageWidgetId>0 ? PageWidget::model()->findByPk($prevPageWidgetId)->order : -1;
                            if ($content->hasAttribute('page_id') && $content->page_id>0)
                                $pageWidget = $widget->setOnPage($content->page_id, $area, -1);
                            else
                                $pageWidget = $widget->setOnPage($pageId, $area, $order);
                            $formArray['action'] .= '&pageWidgetId='.$pageWidget->id;
                        }
                        $content->widget_id = $widget->id;
                		$content->save(false);
                    }
                } else {
                    $content->save(false);
                    $formArray['action'] .= '&modelClass='.get_class($content);
                    $formArray['action'] .= '&recordId='.$content->id;
                }
			}
		}
		$form = new Form($formArray);
        if (isset($widget))
            $form['widget']->model = $widget;
		$form['content']->model = $content;

        // Формируем ответ
        if ($return == 'html') {
            
            if ($ret)
                $this->render('/form', compact('form', 'showTitle', 'caption', 'prevPageWidgetId', 'pageId', 'area'));
            else
                throw new CHttpException(500,Yii::t('cms', 'The requested page does not exist.'));
        } elseif ($return == 'json') {

            echo CJavaScript::jsonEncode(array(
                'pageWidgetId'=>$pageWidget->id,
                'widgetId'=>$widget->id,
                'contentId'=>$content->id,
                'status'=>(int)$ret,
            ));

        }
    }

	/**
     * Обрабатывает запрос на перемещение блока на странице
     *
     * @param string $area название области блоков
     * @param array $pageWidgets массив id блоков в порядке их размещения в области $area
     * @param int $pageWidgetId id блока, который перемещается
     * @param string $return тип возвращаемого ответа (json, html, text)
     */
    public function actionMove($area, $pageWidgetId, $return='html')
	{
        $pageWidgets = $_POST['pageWidgets'];
        $ret = false;
        $pageWidgetId = (int)$pageWidgetId;
        $widget = Widget::model()->findByPk(PageWidget::getWidgetIdById($pageWidgetId));
        if ($widget)
            $ret =  $widget->move($area, $pageWidgets, $pageWidgetId);

        if ($return == 'json') {
            echo CJavaScript::jsonEncode(array(
                'status'=>(int)$ret
            ));
        } else {
            echo (int)$ret;
        }
    }

	/**
     * Обрабатывает запрос удаления блока
     *
     * @param int $widgetId id блока
     * @param mixed $pageWidgetId список id страничных блоков
     * @param boolean $withPage удалять ли блок вместе со связанной страницей
     * @param string $return тип возвращаемого ответа (json, html, text)
     */
	public function actionDelete($widgetId, $withPage=false, $return='text')
	{
        $widgetId = (int)$widgetId;
        $pageWidgetId = $_REQUEST['pageWidgetId'];

        if ($pageWidgetId == 'all') {
            
            $sql = 'UPDATE `' . PageWidget::tableName() . '` as pu
                    INNER JOIN (SELECT `order`, `area`, `page_id` FROM `' . PageWidget::tableName() . '`
                                WHERE `widget_id` = :widget_id) as pu2
                    ON pu.`page_id` = pu2.`page_id`
                    SET pu.`order` = pu.`order`-1
                    WHERE
                        pu.`area` = pu2.`area`
                        AND pu.`order` > pu2.`order`';
            $command = Yii::app()->db->createCommand($sql);
            $command->bindValue(':widget_id', $widgetId, PDO::PARAM_INT);
            $command->execute();
            PageWidget::model()->deleteAll('widget_id = :widget_id', array(':widget_id' => $widgetId));

        } elseif (is_array($pageWidgetId)) {
            
            $sql = 'UPDATE `' . PageWidget::tableName() . '` as pu
                    INNER JOIN (SELECT `order`, `area`, `page_id` FROM `' . PageWidget::tableName() . '`
                                WHERE `id` IN ("'.implode('","',$pageWidgetId).'")
                                    AND `widget_id` = :widget_id) as pu2
                    ON pu.`page_id` = pu2.`page_id`
                    SET pu.`order` = pu.`order`-1
                    WHERE
                        pu.`area` = pu2.`area`
                        AND pu.`order` > pu2.`order`';
            $command = Yii::app()->db->createCommand($sql);
            $command->bindValue(':widget_id', $widgetId, PDO::PARAM_INT);
            $command->execute();
            PageWidget::model()->deleteAll('`id` IN ("'.implode('","',$pageWidgetId).'")');
        }

        $c = PageWidget::model()->count('widget_id = :widget_id', array(':widget_id' => $widgetId));
        if ($c == 0)
        {
            $widget = Widget::model()->findByPk($widgetId);
            if ($widget) {
                // Если нужно, также удаляем ассоциированную страницу
                if ($withPage && $widget->content && $widget->content->hasAttribute('page_id'))
                {
                    $p = Page::model()->findByPk($widget->content->page_id);
                    if ($p)
                        $p->delete();
                }
                $widget->delete();
            }
        }
        $ret = true;
        if ($return == 'json') {
            echo CJavaScript::jsonEncode(array(
                'status'=>(int)$ret
            ));
        } else {
            echo (int)$ret;
        }
    }

    /**
     * Обрабатывает запрос на проверку связи блока с какой-либо страницей
     *
     * @param int $widgetId id блока
     * @param string $return тип возвращаемого ответа (json, html, text)
     */
    public function actionCheck($widgetId, $return='json')
    {
        $ret = array();
        $widgetId = (int)$widgetId;
        $widget = Widget::model()->findByPk($widgetId);
        if ($widget && $widget->content && $widget->content->hasAttribute('page_id')) {
            $page = Page::model()->findByPk($widget->content->page_id);
            $pageWidget = PageWidget::model()->find('widget_id = :widget_id AND page_id = :page_id',
                                    array(':widget_id'=>$widgetId, ':page_id'=>$widget->content->page_id));
            if ($page && $pageWidget) {
                $ret['page'] = array(
                    'title' => $page->title,
                    'url' => $this->createAbsoluteUrl('view/index', array('pageId'=>$page->id, 'alias'=>$page->alias, 'url'=>$page->url)),
                    'similarToParent' => $page->isSimilarTo($page->parent_id, 'all', $widget->id),
                );
            }
        }
        if ($return == 'json') {
            $ret['status'] = 1;
            echo CJavaScript::jsonEncode($ret);
        } else {
            echo (int)$ret;
        }
    }

	/**
     * Возвращает размещения блоков по id блока
     *
     * @param int $widgetId id блока
     */
	public function actionGetPageWidgetsByWidgetId($widgetId)
	{
        $widgetId = (int)$widgetId;
        $sql = 'SELECT `id` FROM `' . PageWidget::tableName() . '` WHERE `widget_id` = :widget_id';
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(':widget_id', $widgetId, PDO::PARAM_INT);
        echo CJavaScript::jsonEncode($command->queryColumn());
	}

	/**
     * Обрабатывает размещение юнита на нескольких страницах
     *
     * @param int $widgetId
     * @param int $pageWidgetId
     * @param array $pageIds
     * @param string $return
     */
    public function actionSet($widgetId, $pageWidgetId, $return='text')
	{
        $widgetId = (int)$widgetId;
        $pageIds = $_POST['pageIds'];
        $widget = Widget::model()->findByPk($widgetId);
        $ret = $widget->setOnPagesOnly($pageIds, $pageWidgetId);
        if ($return == 'json') {
            echo CJavaScript::jsonEncode(array(
                'status'=>(int)$ret
            ));
        } else {
            echo (int)$ret;
        }
	}

    /**
     * Отображает диалог выбора страниц где будет размещен блок
     *
     * @param int $widgetId id блока
     * @param int $pageWidgetId id страничного блока
     */
    public function actionSetDialog($widgetId, $pageWidgetId)
	{
        $widgetId = (int)$widgetId;
        $pageWidgetId = (int)$pageWidgetId;
		$this->render('setDialog',array(
			'model'=>Yii::app()->page->model,
			'widget'=>Widget::model()->findByPk($widgetId),
			'widgetId'=>$widgetId,
			'pageWidgetId'=>$pageWidgetId
		));
	}

    /**
     * Отображает уточнение при удалении юнита
     *
     * @param int $widgetId id блока
     * @param int $pageWidgetId id страничного блока
     */
	public function actionDeleteDialog($widgetId, $pageWidgetId)
	{
        $widgetId = (int)$widgetId;
        $pageWidgetId = (int)$pageWidgetId;
		$this->render('deleteDialog',array(
			'model'=>Yii::app()->page->model,
			'widget'=>Widget::model()->findByPk($widgetId),
			'widgetId'=>$widgetId,
			'pageWidgetId'=>$pageWidgetId
		));
	}

    /**
     * Обрабатывает ajax-запрос к блоку
     *
     * @param int $widgetId id блока
     */
    public function actionAjax($widgetId)
    {
        $widget = Widget::model()->findByPk($widgetId);
        $widget->content->ajax($_REQUEST);
    }

}
