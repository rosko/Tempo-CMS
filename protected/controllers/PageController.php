<?php

class PageController extends Controller
{
	public $layout='//layouts/column2';
	public $_model;
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
				'actions'=>array('index','view','pageunitView'),
				'users'=>array('*'),
			),
			array('allow',
				'actions'=>array('create','update', 'pageunitsByUnit'),
				'users'=>array('@'),
			),
			array('allow',
				'actions'=>array('admin','delete','areaSort', 'pageunitForm',
								 'pageunitAdd', 'pageunitRemove', 'pageForm', 'pageTree',
								 'pageAdd', 'pageFill', 'hasChildren', 'siteSettings',
								 'siteMap', 'pageDeleteConfirm', 'pageDelete', 'getUrl',
								 'pagesSort', 'pageRename', 'pageunitDeleteConfirm'),
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
		if (!isset($_GET['id'])) {
			$_GET['id'] = 1;
		}
		$this->render('view',array(
			'model'=>$this->loadModel()
		));
	}

	// Создает новую страницу
	public function actionPageAdd()
	{
		$this->layout = 'blank';

		$page = Page::defaultObject();
		$form_array = Page::form();
		$form_array['buttons']['go'] = array(
			'type'=>'submit',
			'label'=>'Сохранить и перейти',
			'title'=>'Сохранить и перейти к созданной странице',
		);
		
		$form = new CForm($form_array);
		$form->model = $page;
		
		if ($form->submitted('save') || $form->submitted('go')) {
			$page = $form->model;
			if ($form->validate()) {
				if ($page->save(false)) {
					// Проверяем каждую область и вставляем блоки с родительской страницы в сквозных областях
					$page->fill();
					
					if ($form->submitted('go')) {
						echo CJavaScript::jsonEncode(array('url' => $this->createAbsoluteUrl('page/view', array('id'=>$page->id))));
						Yii::app()->end();
					}
				}
			}
		}		
		
		$this->layout = 'blank';
		$this->render('form', array('form'=>$form));
	}
	
	// Переименовывает название страницы или создает новую
	public function actionPageRename()
	{
		if ($_GET['id']) {
			$page = $this->loadModel();
		} else {
			$page = Page::defaultObject();
			if ($_REQUEST['parent_id']) {
				$page->parent_id = intval($_REQUEST['parent_id']);
			}
			$page->save(false);
			$page->fill();
		}
		if ($_REQUEST['title'] && $page)
		{
			$page->title = $_REQUEST['title'];
			if ($page->save()) {
				if ($pages = $_REQUEST['order'])
				{
					foreach ($pages as $order=>$id)
					{
						if (!$id) { $id = $page->id; }
						$sql = 'UPDATE `' . Page::tableName() . '` SET `order` = :order WHERE `id` = :id';
						$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(':order', intval($order), PDO::PARAM_INT);
						$command->bindValue(':id', intval($id), PDO::PARAM_INT);							
						$command->execute();
					}
				}
				echo $page->id;
				return true;
			}			
		}
		echo '0';
		
	}

	// Редактирует свойства страницы
	public function actionPageForm()
	{
		$page = $this->loadModel();
		$form_array = Page::form();
		if ($page->id != 1) {
			$form_array['buttons']['deletepage'] = array(
				'type'=>'submit',
				'label'=>'Удалить',
				'title'=>'Удалить страницу',
			);
		}
		$form = new CForm($form_array);
		$form->model = $page;
		
		if ($form->submitted('save')) {
			$page = $form->model;
			if ($form->validate()) {
				$page->path = '';
				$page->save(false);
			}
		} elseif ($form->submitted('delete')) {
			$page = $form->model;
			if ($page->id != 1)
			{
				$parent_id = $page->parent_id ? $page->parent_id : 1;
				echo CJavaScript::jsonEncode(array('url' => $this->createAbsoluteUrl('page/view', array('id'=>$parent_id))));
				$page->delete();
				Yii::app()->end();
			}
		}
		
		$this->layout = 'blank';
		$this->render('form', array('form'=>$form));
	}
	
	// Удаляет страницу
	public function actionPageDelete()
	{
		$page = $this->loadModel();
		$page_id = 1;
		if ($_REQUEST['deletechildren'])
		{
			$page_id = $page->parent_id ? $page->parent_id : 1;
			$page->deleteWithChildren();
		}
		elseif ($_REQUEST['movechildren'] && $_REQUEST['newParent'])
		{
			$page_id = $_REQUEST['newParent'];
			$children = $page->children;
			if ($children)
				foreach ($children as $child)
				{
					$child->parent_id = $page_id;
					$child->save(false);
				}
			$page->delete();
		}
		echo CJavaScript::jsonEncode(array('url' => $this->createAbsoluteUrl('page/view', array('id'=>$page_id))));
		Yii::app()->end();
	}

	// Обрабатывает перемещение страниц
	public function actionPagesSort()
	{
		$transaction=Yii::app()->db->beginTransaction();
		try
		{
			if ($_REQUEST['id'] && ($_REQUEST['id'] != 1) &&
				$_REQUEST['parent_id'] && ($_REQUEST['parent_id'] != 0) &&
				$_REQUEST['order'])
			{
				$page = $this->loadModel();
				$page->parent_id = $_REQUEST['parent_id'];
				$page->save(false);


				if ($pages = $_REQUEST['order'])
				{
					foreach ($pages as $order=>$id)
					{
						$sql = 'UPDATE `' . Page::tableName() . '` SET `order` = :order WHERE `id` = :id';
						$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(':order', intval($order), PDO::PARAM_INT);
						$command->bindValue(':id', intval($id), PDO::PARAM_INT);							
						$command->execute();
					}
				}
			}
			$transaction->commit();
			echo '1';
		}
		catch(Exception $e) // в случае ошибки при выполнении запроса выбрасывается исключение
		{
			$transaction->rollBack();
			echo '0';
		}
	}

	// Обрабатывает перемещение юнитов по странице
	public function actionAreaSort()
	{
		$transaction=Yii::app()->db->beginTransaction();
		try
		{
			if ($_REQUEST['area'])
			{
				if ($units = $_REQUEST['cms-pageunit'])
				{
					foreach ($units as $order=>$id)
					{
						$pageunit = PageUnit::model()->findByPk($id);
						$through = Yii::app()->settings->getValue('area'.ucfirst(strtolower($pageunit->area)).'Through');
						if ($through) {
							$sql = 'UPDATE `' . PageUnit::tableName() . '` SET `order` = :order WHERE `unit_id` = :unit_id AND `area` = :area';
						} else {
							$sql = 'UPDATE `' . PageUnit::tableName() . '` SET `order` = :order WHERE `id` = :id';
						}

						$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(':order', intval($order), PDO::PARAM_INT);
						if ($through) {
							$command->bindValue(':unit_id', intval($pageunit->unit_id), PDO::PARAM_INT);
							$command->bindValue(':area', $pageunit->area, PDO::PARAM_INT);
						} else {
							$command->bindValue(':id', intval($id), PDO::PARAM_INT);							
						}
						$command->execute();
					}
				}
				if ($_REQUEST['old_area'] && $_REQUEST['pageunit_id'])
				{
					$through = Yii::app()->settings->getValue('area'.ucfirst(strtolower($_REQUEST['area'])).'Through');
					$old_through = Yii::app()->settings->getValue('area'.ucfirst(strtolower($_REQUEST['old_area'])).'Through');
					$pageunit = PageUnit::model()->findByPk($_REQUEST['pageunit_id']);

					if ($through && $old_through)
					{
						// Перенести блок на всех страницах
						$sql = 'UPDATE `' . PageUnit::tableName() . '` SET `area` = :area WHERE `unit_id` = :unit_id AND `area` = :old_area LIMIT 1';
						$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(':area', $_REQUEST['area'], PDO::PARAM_STR);
						$command->bindValue(':old_area', $_REQUEST['old_area'], PDO::PARAM_STR);
						$command->bindValue(':unit_id', intval($pageunit->unit_id), PDO::PARAM_INT);
						$command->execute();			
					}
					elseif (!$through && !$old_through)
					{
						// Перенести этот блок
						$sql = 'UPDATE `' . PageUnit::tableName() . '` SET `area` = :area WHERE `id` = :id LIMIT 1';
						$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(':area', $_REQUEST['area'], PDO::PARAM_STR);
						$command->bindValue(':id', intval($_REQUEST['pageunit_id']), PDO::PARAM_INT);
						$command->execute();			
					}
					elseif ($through && !$old_through)
					{
						// Перенести этот блок и добавить его по всем страницам
						$sql = 'UPDATE `' . PageUnit::tableName() . '` SET `area` = :area WHERE `id` = :id LIMIT 1';
						$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(':area', $_REQUEST['area'], PDO::PARAM_STR);
						$command->bindValue(':id', intval($_REQUEST['pageunit_id']), PDO::PARAM_INT);
						$command->execute();

						$sql = 'SELECT id FROM `' . Page::tableName() . '` WHERE id <> ' . $pageunit->page_id;
						$ids = Yii::app()->db->createCommand($sql)->queryColumn();
						$sql = 'INSERT INTO `' . PageUnit::tableName() . '` (`page_id`, `unit_id`, `order`, `area`) VALUES ';
						$sql_arr = array();
						foreach ($ids as $id)
						{
							$sql_arr[] = '('.intval($id).', '.intval($pageunit->unit_id).', '.intval($pageunit->order).', :area)';
						}
						$sql .= implode(',', $sql_arr);
						$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(':area', $_REQUEST['area']);
						$command->execute();
		
						$sql = 'UPDATE `' . PageUnit::tableName() . '` SET `order`=`order`+1 WHERE `area` = :area AND `order` > :order';
						$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(':area', $_REQUEST['area']);
						$command->bindValue(':order', $pu->order);
						$command->execute();
					}
					elseif (!$through && $old_through)
					{
						// Перенести этот блок, а на других страницах убрать его
						$sql = 'UPDATE `' . PageUnit::tableName() . '` SET `area` = :area WHERE `id` = :id LIMIT 1';
						$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(':area', $_REQUEST['area'], PDO::PARAM_STR);
						$command->bindValue(':id', intval($_REQUEST['pageunit_id']), PDO::PARAM_INT);
						$command->execute();

						$sql = 'DELETE FROM `' . PageUnit::tableName() . '` WHERE `area` = :area AND `unit_id` = :unit_id AND `id` != :id';
						$command = Yii::app()->db->createCommand($sql);
						$command->bindValue(':area', $_REQUEST['old_area']);
						$command->bindValue(':unit_id', $pageunit->unit_id);
						$command->bindValue(':id', intval($_REQUEST['pageunit_id']), PDO::PARAM_INT);
						$command->execute();

					}

				}
			}
			$transaction->commit();
			echo '1';
		}
		catch(Exception $e) // в случае ошибки при выполнении запроса выбрасывается исключение
		{
			$transaction->rollBack();
			echo '0';
		}
	}
	
	// Отображает юнит
	public function actionPageunitView()
	{
		$unit = PageUnit::model()->with('unit')->findByPk($_REQUEST['pageunit_id']);
		
        $this->renderPartial('application.units.views.Unit'.ucfirst(strtolower($unit->unit->type)),
			array('unit'=>$unit->unit,
                'content'=>$unit->unit->content,
				'page'=>$this->loadModel()));
		
	}
	
	// Создает новый юнит
	public function actionPageunitAdd()
	{
		if (isset($_REQUEST['pageunit_id']) && isset($_REQUEST['area']) && isset($_REQUEST['type']) && isset($_REQUEST['page_id']))
		{
			$unit = new Unit;
			$unit->type = $_REQUEST['type'];
			$className = 'Unit'.ucfirst(strtolower($_REQUEST['type']));
			$unit->title = $className::NAME;
			$unit->create = new CDbExpression('NOW()');
			$unit->save();

			$pu = PageUnit::model()->findByPk($_REQUEST['pageunit_id']);
			$sql = 'UPDATE `' . PageUnit::tableName() . '` SET `order`=`order`+1 WHERE `page_id` = :page_id AND `area` = :area AND `order` > :order';
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(':page_id', intval($_REQUEST['page_id']));
			$command->bindValue(':area', $_REQUEST['area']);
			$command->bindValue(':order', $pu->order);
			$command->execute();
			
			$pageunit = new PageUnit;
			$pageunit->page_id = intval($_REQUEST['page_id']);
			$pageunit->unit_id = $unit->id;
			$pageunit->order = $pu->order+1;
			$pageunit->area = $_REQUEST['area'];
			$pageunit->save();
			
			$through = Yii::app()->settings->getValue('area'.ucfirst(strtolower($pageunit->area)).'Through');
			// Если область является сквозной, то разместить новый блок на всех страницах
			if ($through) {
				$sql = 'SELECT id FROM `' . Page::tableName() . '` WHERE id <> ' . $pageunit->page_id;
				$ids = Yii::app()->db->createCommand($sql)->queryColumn();
				$sql = 'INSERT INTO `' . PageUnit::tableName() . '` (`page_id`, `unit_id`, `order`, `area`) VALUES ';
				$sql_arr = array();
				foreach ($ids as $id)
				{
					$sql_arr[] = '('.intval($id).', '.intval($unit->id).', '.intval($pageunit->order).', :area)';
				}
				$sql .= implode(',', $sql_arr);
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(':area', $pageunit->area);
				$command->execute();

				$sql = 'UPDATE `' . PageUnit::tableName() . '` SET `order`=`order`+1 WHERE `page_id` != :page_id AND `area` = :area AND `order` > :order';
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(':page_id', intval($_REQUEST['page_id']));
				$command->bindValue(':area', $_REQUEST['area']);
				$command->bindValue(':order', $pu->order);
				$command->execute();
			}
			
			if (method_exists($className, 'defaultObject')) {
				$content = $className::defaultObject();
			} else {
				$content = new $className;				
			}
			$content->unit_id = $unit->id;
			$content->save(false);
			
			echo CJavaScript::jsonEncode(array('pageunit_id'=>$pageunit->id,'unit_id'=>$unit->id));
		} else echo '0';
	}
	
	// Редактирует свойства юнита
	public function actionPageunitForm()
	{
		$unit_class = 'Unit'.ucfirst(strtolower($_REQUEST['unit_type']));
		if ($_REQUEST['pageunit_id']) {
			$pageunit = PageUnit::model()->with('unit')->findByPk($_REQUEST['pageunit_id']);
			$unit = $pageunit->unit;
			$content = $unit->content;
		} else {
			$unit = new Unit;
			$content = new $unit_class;
		}
		$unit_form_array = $unit_class::form();
		$unit_form_array['type'] = 'form';
		$form_array = array(
			'title'=>$unit_class::NAME,
			'elements'=>array(
				'unit'=>array(
					'type'=>'form',
					'elements'=>array(
						'title'=>array(
							'type'=>'text',
							'maxlength'=>255,
							'size'=>60
						)
					)
				),
				'content'=> $unit_form_array
			),
			'buttons'=>array(
				'save'=>array(
					'type'=>'submit',
					'label'=>'Сохранить',
					'title'=>'Сохранить и закрыть окно'
				),
				'apply'=>array(
					'type'=>'submit',
					'label'=>'Применить',
					'title'=>'Сохранить и продолжить редактирование'
				),
			)
		);

		$form = new CForm($form_array);
		$form['unit']->model = $unit;
		$form['content']->model = $content;				
		
		if ($form->submitted('save') || $form->submitted('apply')) {
			$unit = $form['unit']->model;
			$content = $form['content']->model;
			if ($form->validate()) {
				$content->unit_id = $unit->id;
				$unit->modify = new CDbExpression('NOW()');
				if ($unit->save(false)) {
					$content->save(false);
				}
			}
		}
		$form = new CForm($form_array);
		$form['unit']->model = $unit;
		$form['content']->model = $content;
		
		$this->layout = 'empty';
		$this->render('form', array('form'=>$form));
	}
	
	// Удаляет юнит
	public function actionPageunitRemove()
	{
		if (isset($_REQUEST['unit_id']) && (isset($_REQUEST['pageunit_id']) || isset($_REQUEST['page_ids'])))
		{
/*
			if (is_array($_REQUEST['pageunit_id']) && isset($_REQUEST['pageunit_id'][0])) {
				$pageunit = PageUnit::model()->findByPk($_REQUEST['pageunit_id'][0]);
				$through = Yii::app()->settings->getValue('area'.ucfirst(strtolower($pageunit->area)).'Through');
			} else
				$through = false;
*/
			if (isset($_REQUEST['pageunit_id']))
			{
				if ($_REQUEST['pageunit_id'] == 'all') {
					PageUnit::model()->deleteAll('unit_id = :unit_id', array(':unit_id' => $_REQUEST['unit_id']));
				} elseif (is_array($_REQUEST['pageunit_id'])) {
					PageUnit::model()->deleteAll('`id` IN ("'.implode('","',$_REQUEST['pageunit_id']).'")');
				}
			}
			elseif (isset($_REQUEST['page_ids']) && is_array($_REQUEST['page_ids']))
			{
				PageUnit::model()->deleteAll('unit_id = :unit_id AND `page_id` IN ("'.implode('","',$_REQUEST['page_ids']).'")',
											 array(':unit_id' => $_REQUEST['unit_id']));
			}
			
			$c = PageUnit::model()->count('unit_id = :unit_id', array(':unit_id' => $_REQUEST['unit_id']));
			if ($c == 0)
			{
				$unit = Unit::model()->findByPk($_REQUEST['unit_id']);
				$unit->content->delete();
				$unit->delete();				
			}
			echo '1';
		}		
	}
	
	// Редактирует свойства сайта
	public function actionSiteSettings()
	{
		//$settingsForm = ;
		$form_array = SiteSettingsForm::form();
		$form_array['title'] = 'Настройки сайта';
		$form_array['buttons'] = array(
				'save'=>array(
					'type'=>'submit',
					'label'=>'Сохранить',
					'title'=>'Сохранить и закрыть окно'
				),
			);
		$form = new CForm($form_array);
		$form->model = clone Yii::app()->settings->model;

		if ($form->submitted('save') && $form->model->validate()) {
			Yii::app()->settings->saveAll($form->model->getAttributes());
		}

		$this->layout = 'blank';
		$this->render('form', array('form'=>$form));
		
	}
	
	// Отображает карту сайта
	public function actionSiteMap()
	{
		$tree = Page::model()->getTree();
		$initially_open = array();
		$opened_levels = Yii::app()->request->isAjaxRequest ? 2 : 3;
		foreach ($tree as $pages)
		{
			foreach ($pages as $p)
			{
				if (substr_count($p->path,',') < $opened_levels)
					$initially_open[] = 'page-'.$p->id;
			}
		}
		if (Yii::app()->request->isAjaxRequest) {
			$this->layout = 'blank';			
		}
		$this->render('pagemap', compact('tree', 'initially_open'));
	}
	
	// Отображает дерево страниц
	public function actionPageTree()
	{
		if (isset($_GET['id']) && !empty($_GET['id'])) {
			$model = $this->loadModel();
			if (!$_REQUEST['tree_id']) {
				$_REQUEST['tree_id'] = 'pagetree'.$_GET['id'];
			}
			$tree = Page::model()->getTree($model->id);
		} else {
 			if (!$_REQUEST['tree_id']) {
				$_REQUEST['tree_id'] = 'pagetree';
			}
			$tree = Page::model()->getTree();
		}
		$this->layout = 'blank';
		$this->render('pagetree', array('tree' => $tree, 'tree_id' => $_REQUEST['tree_id'],
										'multiple' => (bool)$_REQUEST['multiple'],
										'enabledOnly' => $_REQUEST['enabledOnly'],
										'disabled' => $_REQUEST['disabled']));
	}
	
	// Отображает диалог для заполнения пустой страницы
	public function actionPageFill()
	{
		$this->layout = 'empty';
		$this->render('fill');
	}
	
	// Отображает уточнение при удалении страницы
	public function actionPageDeleteConfirm()
	{
		$this->layout = 'blank';
		$this->render('pageDeleteConfirm',array(
			'model'=>$this->loadModel()
		));
		
	}
	
	// Отображает уточнение при удалении юнита
	public function actionPageunitDeleteConfirm()
	{
		$this->layout = 'blank';
		$this->render('pageunitDeleteConfirm',array(
			'model'=>$this->loadModel(),
			'unit'=>Unit::model()->findByPk($_REQUEST['unit_id']),
			'unit_id'=>$_REQUEST['unit_id'],
			'pageunit_id'=>$_REQUEST['pageunit_id']
		));
		
	}
	// Возвращает ссылку по id страницы
	public function actionGetUrl()
	{
		echo $this->createAbsoluteUrl('page/view', array('id'=>intval($_GET['id'])));
	}

	// Возвращает размещения юнитов по юниту
	public function actionPageunitsByUnit()
	{
		if (isset($_REQUEST['unit_id']))
		{
			$sql = 'SELECT `id` FROM `' . PageUnit::tableName() . '` WHERE `unit_id` = :unit_id';
			$command = Yii::app()->db->createCommand($sql);
			$command->bindValue(':unit_id', intval($_REQUEST['unit_id']), PDO::PARAM_INT);
			$ids = $command->queryColumn();
			echo CJavaScript::jsonEncode($ids);
		}
	}
	
	// Возвращает количество дочерних страниц
	public function actionHasChildren()
	{
		if ($_REQUEST['id'])
		{
			echo Page::model()->count('parent_id = :parent_id',array(':parent_id'=>intval($_REQUEST['id'])));
		}
	}
	
	public function loadModel()
	{
		if($this->_model===null)
		{
			if(isset($_GET['id']))
				$this->_model=Page::model()->findbyPk($_GET['id']);
			if($this->_model===null)
				throw new CHttpException(404,'The requested page does not exist.');
		}
		return $this->_model;
	}

	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='page-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
