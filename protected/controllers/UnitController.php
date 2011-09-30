<?php
/**
 * UnitController
 *
 * @author Alexey Volkov <a@insvit.com>
 * @link http://www.insvit.com/
 * @copyright Copyright &copy; 2010-2011 Alexey Volkov
 * 
 */

/**
 * UnitController - это класс контроллера, который отвечает за обработку ajax-запросов
 * для управления блоками
 */
class UnitController extends Controller
{

    /**
     * Обрабатывает запрос на редактирование блока
     *
     * @param int $pageUnitId id редактируемого страничного блока
     * @param int $unitId id редактируемого блока
     * @param int $pageId id страницы, на которой размещается блок
     * (используется, если предполагается создание нового блока)
     * @param int $prevPageUnitId id блока, после которого размещается текущий блок
     * (используется, если предполагается создание нового блока)
     * @param string $area название области страницы где размещается блок
     * (используется, если предполагается создание нового блока)
     * @param string $type тип блока
     * (используется, если предполагается создание нового блока)
     * @param string $className имя класса редактируемой записи
     * @param int $recordId id редактируемой записи
     * @param int $sectionId
     * @param string foreignAttribute
     * @param bool $makePage
     * @param string $return тип возвращаемого ответа (json, html)
     */
    public function actionEdit($pageUnitId=0, $unitId=0, $pageId=0, $prevPageUnitId=0, $area='main', $type='', $className='', $recordId=0, $sectionId=0, $foreignAttribute='', $makePage=false, $return='html')
    {
        $ret = true;
        // Обрабатываем входящие параметры и находим (или создаем) необходимые объекты
        $pageUnitId = (int)$pageUnitId;
        $unitId = (int)$unitId;
        $recordId = (int)$recordId;
        $pageId = (int)$pageId;
        $unit = null;
        // Если указан id страничного блока
        if ($pageUnitId>0) {
            $pageUnit = PageUnit::model()->with('unit')->findByPk($pageUnitId);
            if ($pageUnit) {
                $unit = $pageUnit->unit;
                $content = $unit->content;
                $content->scenario = 'edit';
            }
        // Если указан id блока в общем
        } elseif ($unitId>0) {
            $unit = Unit::model()->findByPk($unitId);
            if ($unit) {
                $content = $unit->content;
                $content->scenario = 'edit';
            }
        // Если указан класс и id любого объекта
        } elseif ($recordId>0 && $className) {
            $content = call_user_func(array($className, 'model'))->findByPk($recordId);
            $content->scenario = 'edit';
        // Если указан только тип или класс
        } elseif ($type || $className) {
            if ($type)
                $className = Unit::getClassNameByUnitType($type);
            if (method_exists($className, 'defaultObject')) {
                $content = call_user_func(array($className, 'defaultObject'));
            } else {
                $content = new $className;
            }
            $content->scenario = 'add';
        } else {
            $ret = false;
        }
        if (!$unit && $content) {
            if (!empty($content->unit_id)) {
                $unit =  $content->unit;
            } elseif ($content->scenario == 'add') {
                $unit = new Unit;
            }
        }

        $className = get_class($content);
        $langs = array_keys(I18nActiveRecord::getLangs(Yii::app()->language));
        // Если блок новый, заполняем его исходными данными
        if ($unit && $unit->isNewRecord) {
			$unit->type = Unit::getUnitTypeByClassName($className);
			$unit->title = call_user_func(array($className, 'unitName'));
            foreach ($langs as $lang) {
                $unit->{$lang.'_title'} = call_user_func(array($className, 'unitName'), $lang);
            }
        }
        // Если указывается внешний ключ
        $sectionId = (int)$sectionId;
        if ($sectionId && $foreignAttribute && $content->hasAttribute($foreignAttribute)) {
            $content->{$foreignAttribute} = $sectionId;
        }

        // Делаем форму редактирования
        $id = 'UnitEdit'.$className;

        $unitFormArray = call_user_func(array($className, 'form'));
        $unitFormArray['type'] = 'form';
        $unitFormArray['id'] = $id;
        $showTitle = true;
        if (isset($unitFormArray['title'])) {
            if ($unitFormArray['title'] === false)
                $showTitle = false;
            unset($unitFormArray['title']);
        }
        //  В окно редактирования свойств подключаем диалог для управления размещением
        //  todo: нужно исправить урл
//        if (isset($pageUnit)) {
//            $unitFormArray['elements'][] = Form::tab('Размещение', '/?r=unit/setDialog&pageId='.$pageUnit->page_id.'&unitId='.$pageUnit->unit_id.'&pageUnitId='.$pageUnit->id);
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
            if (a[0] == 'pageUnitId') {
                var pageUnitId = a[1];
            }
            if (a[0] == 'unitId') {
                var unitId = a[1];
            }
        }
        var prevPageUnitId = '{$prevPageUnitId}';
        var areaName = '{$area}';
        var type = '{$type}';
        var pageId = '{$pageId}';
        if ($('#cms-pageunit-'+pageUnitId).length==0) {
            if (prevPageUnitId != '0') {
                var prevPageUnit = $('#cms-pageunit-'+prevPageUnitId);
            } else {
                var prevPageUnit = $('#cms-area-'+areaName).find('.cms-empty-area-buttons').eq(0);
            }
            prevPageUnit.after('<div id="cms-pageunit-'+pageUnitId+'" class="cms-pageunit cms-unit-'+type+'" rel="'+type+'" rev="'+unitId+'" style="cursor:move;"></div>');
        }
        var pageUnit = $('#cms-pageunit-'+pageUnitId);
        if (pageUnit.length) {
            var origBg = pageUnit.css('backgroundColor');
            cmsReloadPageUnit(pageUnitId, '.cms-pageunit[rev='+unitId+']', function() {
                cmsAreaEmptyCheck();
            });
            pageUnit.css('backgroundColor', '#FFFF00').animate({
                backgroundColor: origBg
            }, 2500);
            $.scrollTo('#cms-pageunit-'+pageUnitId, 'normal', {
                offset: -10
            });
        }


    }
});
}
JS;

        if (substr($unitFormArray['elements'][0],0,2)!=Form::TAB_DELIMETER
                || substr($unitFormArray['elements'][0],-2)!=Form::TAB_DELIMETER)
        {
            $formArray['title']='';
        }
        if (method_exists($className, 'unitName')) {
            if (is_subclass_of($className, 'Content')) {
                $caption = array(
                    'icon' => constant($className.'::ICON'),
                    'label' => call_user_func(array($className, 'unitName')),
                );
            } else {
                $caption = array(
                    'icon' => constant($className.'::ICON'),
                    'label' => $content->unitName(),
                );
            }
        } else {
        }
        if (isset($unit)) {
            $formArray['elements']['unit'] = array(
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
            if (Yii::app()->settings->getValue('showUnitAppearance')) {
                $formArray['elements']['unit']['elements']['template'] = array(
                    'type'=>'TemplateSelect',
                    'className'=>$className,
                    'empty'=>Yii::t('cms', '«accordingly to general settings»'),
                );
                $unitFormArray['elements'][] = Form::tab(Yii::t('cms', 'Appearance'));
            }
        }
        $formArray['elements']['content'] = $unitFormArray;

		$form = new Form($formArray);
        if (isset($unit))
            $form['unit']->model = $unit;
		$form['content']->model = $content;

        // ajax-валидация
        if (isset($unit)) {
            $this->performAjaxValidation(array($unit, $content));
        } else
            $this->performAjaxValidation($content);

		// Проверка и сохранение
        if ($form->submitted('save') || $form->submitted('apply')) {
            if (isset($unit))
                $unit = $form['unit']->model;
			$content = $form['content']->model;
			if ($form->validate()) {
                if (isset($unit)) {                    
                    // Если нужно, создаем виртуальную страницу
                    if ($makePage && $content->hasAttribute('page_id') && $pageId>0) {
                        if ($content->page_id)
                            $page = Page::model()->findByPk($content->page_id);
                        if (empty($page)) {
                            $page = new Page;
                            $page->parent_id = $pageId;
                            $page->active = true;
                        }
                        $page->virtual = true;
                        $page->title = $unit->title;
                        $page->alias = Page::sanitizeAlias($unit->title);
                        foreach ($langs as $lang) {
                            $page->{$lang.'_title'} = $unit->{$lang.'_title'};
                            $page->{$lang.'_alias'} = Page::sanitizeAlias($unit->{$lang.'_title'});
                        }
                        $allLangs = array_keys(I18nActiveRecord::getLangs());
                        $allLangs[] = '';
                        foreach($allLangs as $lang) {
                            $prefix = ($lang != '') ? $lang.'_' : '';
                            $urlParam = $prefix.'url';
                            $aliasParam = $prefix.'alias';
                            $titleParam = $prefix.'title';
                            $page->$urlParam = $page->generateUrl(true, $aliasParam);
                            $counter=1;
                            while (!$page->validate(array($urlParam))) {
                                $counter++;
                                $page->$aliasParam = Page::sanitizeAlias($unit->$titleParam.' '.$counter);
                                $page->$urlParam = $page->generateUrl(true, $aliasParam);
                            }
                        }
                        $ret = $page->save();
                        if ($ret) {
                            $content->page_id = $page->id;
                            $page->fill();
                        }
                    }
                    $isUnitNew = $unit->isNewRecord;
            		if ($ret && $unit->save(false)) {
                        $formArray['action'] .= '&unitId='.$unit->id;
            			// Если блок новый, размещаем его на текущей странице
                        if ($isUnitNew) {
                            $prevPageUnitId = (int)$prevPageUnitId;
                            $order = $prevPageUnitId>0 ? PageUnit::model()->findByPk($prevPageUnitId)->order : -1;
                            if ($content->hasAttribute('page_id') && $content->page_id>0)
                                $pageUnit = $unit->setOnPage($content->page_id, $area, -1);
                            else
                                $pageUnit = $unit->setOnPage($pageId, $area, $order);
                            $formArray['action'] .= '&pageUnitId='.$pageUnit->id;
                        }
                        $content->unit_id = $unit->id;
                		$content->save(false);
                    }
                } else {
                    $content->save(false);
                    $formArray['action'] .= '&className='.get_class($content);
                    $formArray['action'] .= '&recordId='.$content->id;
                }
			}
		}
		$form = new Form($formArray);
        if (isset($unit))
            $form['unit']->model = $unit;
		$form['content']->model = $content;

        // Формируем ответ
        if ($return == 'html') {
            
            if ($ret)
                $this->render('/form', compact('form', 'showTitle', 'caption', 'prevPageUnitId', 'pageId', 'area'));
            else
                throw new CHttpException(500,Yii::t('cms', 'The requested page does not exist.'));
        } elseif ($return == 'json') {

            echo CJavaScript::jsonEncode(array(
                'pageUnitId'=>$pageUnit->id,
                'unitId'=>$unit->id,
                'contentId'=>$content->id,
                'status'=>(int)$ret,
            ));

        }
    }

	/**
     * Обрабатывает запрос на перемещение блока на странице
     *
     * @param string $area название области блоков
     * @param array $pageUnits массив id блоков в порядке их размещения в области $area
     * @param int $pageUnitId id блока, который перемещается
     * @param string $return тип возвращаемого ответа (json, html, text)
     */
    public function actionMove($area, $pageUnitId, $return='html')
	{
        $pageUnits = $_POST['pageUnits'];
        $ret = false;
        $pageUnitId = (int)$pageUnitId;
        $unit = Unit::model()->findByPk(PageUnit::getUnitIdById($pageUnitId));
        if ($unit)
            $ret =  $unit->move($area, $pageUnits, $pageUnitId);

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
     * @param int $unitId id блока
     * @param mixed $pageUnitId список id страничных блоков
     * @param boolean $withPage удалять ли блок вместе со связанной страницей
     * @param string $return тип возвращаемого ответа (json, html, text)
     */
	public function actionDelete($unitId, $withPage=false, $return='text')
	{
        $unitId = (int)$unitId;
        $pageUnitId = $_REQUEST['pageUnitId'];

        if ($pageUnitId == 'all') {
            
            $sql = 'UPDATE `' . PageUnit::tableName() . '` as pu
                    INNER JOIN (SELECT `order`, `area`, `page_id` FROM `' . PageUnit::tableName() . '`
                                WHERE `unit_id` = :unit_id) as pu2
                    ON pu.`page_id` = pu2.`page_id`
                    SET pu.`order` = pu.`order`-1
                    WHERE
                        pu.`area` = pu2.`area`
                        AND pu.`order` > pu2.`order`';
            $command = Yii::app()->db->createCommand($sql);
            $command->bindValue(':unit_id', $unitId, PDO::PARAM_INT);
            $command->execute();
            PageUnit::model()->deleteAll('unit_id = :unit_id', array(':unit_id' => $unitId));

        } elseif (is_array($pageUnitId)) {
            
            $sql = 'UPDATE `' . PageUnit::tableName() . '` as pu
                    INNER JOIN (SELECT `order`, `area`, `page_id` FROM `' . PageUnit::tableName() . '`
                                WHERE `id` IN ("'.implode('","',$pageUnitId).'")
                                    AND `unit_id` = :unit_id) as pu2
                    ON pu.`page_id` = pu2.`page_id`
                    SET pu.`order` = pu.`order`-1
                    WHERE
                        pu.`area` = pu2.`area`
                        AND pu.`order` > pu2.`order`';
            $command = Yii::app()->db->createCommand($sql);
            $command->bindValue(':unit_id', $unitId, PDO::PARAM_INT);
            $command->execute();
            PageUnit::model()->deleteAll('`id` IN ("'.implode('","',$pageUnitId).'")');
        }

        $c = PageUnit::model()->count('unit_id = :unit_id', array(':unit_id' => $unitId));
        if ($c == 0)
        {
            $unit = Unit::model()->findByPk($unitId);
            if ($unit) {
                // Если нужно, также удаляем ассоциированную страницу
                if ($withPage && $unit->content && $unit->content->hasAttribute('page_id'))
                {
                    $p = Page::model()->findByPk($unit->content->page_id);
                    if ($p)
                        $p->delete();
                }
                $unit->delete();
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
     * @param int $unitId id блока
     * @param string $return тип возвращаемого ответа (json, html, text)
     */
    public function actionCheck($unitId, $return='json')
    {
        $ret = array();
        $unitId = (int)$unitId;
        $unit = Unit::model()->findByPk($unitId);
        if ($unit && $unit->content && $unit->content->hasAttribute('page_id')) {
            $page = Page::model()->findByPk($unit->content->page_id);
            $pageUnit = PageUnit::model()->find('unit_id = :unit_id AND page_id = :page_id',
                                    array(':unit_id'=>$unitId, ':page_id'=>$unit->content->page_id));
            if ($page && $pageUnit) {
                $ret['page'] = array(
                    'title' => $page->title,
                    'url' => $this->createAbsoluteUrl('view/index', array('pageId'=>$page->id, 'alias'=>$page->alias, 'url'=>$page->url)),
                    'similarToParent' => $page->isSimilarTo($page->parent_id, 'all', $unit->id),
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
     * @param int $unitId id блока
     */
	public function actionGetPageUnitsByUnitId($unitId)
	{
        $unitId = (int)$unitId;
        $sql = 'SELECT `id` FROM `' . PageUnit::tableName() . '` WHERE `unit_id` = :unit_id';
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(':unit_id', $unitId, PDO::PARAM_INT);
        echo CJavaScript::jsonEncode($command->queryColumn());
	}

	/**
     * Обрабатывает размещение юнита на нескольких страницах
     *
     * @param int $unitId
     * @param int $pageUnitId
     * @param array $pageIds
     * @param string $return
     */
    public function actionSet($unitId, $pageUnitId, $return='text')
	{
        $unitId = (int)$unitId;
        $pageIds = $_POST['pageIds'];
        $unit = Unit::model()->findByPk($unitId);
        $ret = $unit->setOnPagesOnly($pageIds, $pageUnitId);
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
     * @param int $unitId id блока
     * @param int $pageUnitId id страничного блока
     */
    public function actionSetDialog($unitId, $pageUnitId)
	{
        $unitId = (int)$unitId;
        $pageUnitId = (int)$pageUnitId;
		$this->render('setDialog',array(
			'model'=>Yii::app()->page->model,
			'unit'=>Unit::model()->findByPk($unitId),
			'unitId'=>$unitId,
			'pageUnitId'=>$pageUnitId
		));
	}

    /**
     * Отображает уточнение при удалении юнита
     *
     * @param int $unitId id блока
     * @param int $pageUnitId id страничного блока
     */
	public function actionDeleteDialog($unitId, $pageUnitId)
	{
        $unitId = (int)$unitId;
        $pageUnitId = (int)$pageUnitId;
		$this->render('deleteDialog',array(
			'model'=>Yii::app()->page->model,
			'unit'=>Unit::model()->findByPk($unitId),
			'unitId'=>$unitId,
			'pageUnitId'=>$pageUnitId
		));
	}

    /**
     * Страница инсталляции/деинсталляции блоков
     */
    public function actionInstall()
    {
        $allUnits = Unit::getAllUnits();
        $errors = array();
        if (isset($_POST['Units'])) {
            $units = array_keys($_POST['Units']);
            Unit::install($units);
            $uninstall = array_diff(array_keys($allUnits), $units);
            foreach ($uninstall as $i=>$className) {
                $sql = 'SELECT count(*) FROM `' . Unit::tableName() . '` WHERE `type` = :type';
                $command = Yii::app()->db->createCommand($sql);
                $command->bindValue(':type', Unit::getUnitTypeByClassName($className), PDO::PARAM_STR);
                $exists = $command->queryScalar();
                if ($exists) {
                    unset($uninstall[$i]);
                    $errors[] = Yii::t('cms', 'Can\`t unistall "{name}"', array('{name}'=>$allUnits[$className]['name']));
                }
            }
            Unit::uninstall($uninstall);
            $allUnits = Unit::getAllUnits();
        }

        $this->render('install', array(
            'units' => $allUnits,
            'errors' => $errors,
        ));
    }

    /**
     * Обрабатывает ajax-запрос к блоку
     *
     * @param int $unitId id блока
     */
    public function actionAjax($unitId)
    {
        $unit = Unit::model()->findByPk($unitId);
        $unit->content->ajax($_REQUEST);
    }

}