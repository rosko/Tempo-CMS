<?php
/**
 * PageController
 *
 * @author Alexey Volkov <a@insvit.com>
 * @link http://www.insvit.com/
 * @copyright Copyright &copy; 2010-2011 Alexey Volkov
 *
 */

/**
 * PageController - это класс контроллера, который отвечает за обработку ajax-запросов
 * для управления страницами
 */
class PageController extends Controller
{
	public function filters()
	{
		return array('accessControl');
	}

	public function accessRules()
	{
		return array(
			array('allow',
				'actions'=>array('add', 'delete', 'deleteDialog', 'edit', 
                    'fill', 'rename', 'sort', 'tree'),
				'users'=>array('@'),
			),
            array('allow',
                'actions'=>array('getUrl', 'hasChildren'),
                'users'=>array('*'),                
            ),
			array('deny',
				'users'=>array('*'),
			),
		);
	}
    /**
     * Создает новую страницу
     */
	public function actionAdd()
	{
		$page = Page::defaultObject();
        if (isset($_POST['Page'])) {
            $page->parent_id = $_POST['Page']['parent_id'];
            $_POST['Page']['url'] = $page->parent->url . '/' . $_POST['Page']['alias'];
            $langs = array_keys(I18nActiveRecord::getLangs(Yii::app()->language));
            foreach ($langs as $lang) {
                $alias_param = $lang.'_alias';
                $url_param = $lang.'_url';
                if (isset($_POST['Page'][$alias_param])) {
                    $_POST['Page'][$url_param] = $page->parent->$url_param . '/' . $_POST['Page'][$alias_param];
                }
            }
        }

        $form_array = Page::form();
        $form_array['id'] = 'PageAdd';
        $form_array['activeForm'] = Form::ajaxify($form_array['id']);
		$form_array['buttons']['go'] = array(
			'type'=>'submit',
			'label'=>Yii::t('cms', 'Save and Go'),
			'title'=>Yii::t('cms', 'Save and Go to the new page'),
		);
		
		$form = new Form($form_array);
        $form->id = $form_array['id'];
		$form->model = $page;

        $this->performAjaxValidation($page);
		
		if ($form->submitted('save') || $form->submitted('go')) {
			$page = $form->model;
//			if ($form->validate()) {
				if ($page->save(false)) {
					// Проверяем каждую область и вставляем блоки с родительской страницы в сквозных областях
					$page->fill();
					
					if ($form->submitted('go')) {
                        $alias_param = Yii::app()->language . '_alias';
                        $url = $this->createAbsoluteUrl('view/index', array('pageId'=>$page->id, 'alias'=>$page->{$alias_param}, 'url'=>$page->generateUrl(true,$alias_param)));
						echo CJavaScript::jsonEncode(array(
                            'url' => $url,
                            'id' => $page->id,
                        ));
                        Yii::app()->user->setFlash('add', 'Page has been created successfully');
						Yii::app()->end();
					}
				}
//			}
		}
        $caption = array(
            'icon' => 'add',
            'label' => Yii::t('cms', 'New page'),
        );
        $this->render('/form', array('form'=>$form, 'caption' => $caption));
	}

    /**
     * Переименовывает название страницы
     *
     * @param int $pageId id страницы
     */
	public function actionRename($pageId=0)
	{
        $pageId = (int)$pageId;
		if ($pageId) {
			$page = Yii::app()->page->model;
		} else {
			$page = Page::defaultObject();
			if ($_REQUEST['parentId']) {
				$page->parent_id = intval($_REQUEST['parentId']);
            }
            $page->save(false);
            $page->fill();
		}
		if (isset($_REQUEST['title']) && $page)
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

    /**
     * Редактирует свойства страницы
     *
     * @param int $pageId id редактируемой страницы
     */
	public function actionEdit($pageId)
	{
		$page = Yii::app()->page->model;
		$form_array = Page::form();
        $form_array['id'] = sprintf('%x',crc32(serialize(array_keys($page->attributes))));
        $form_array['buttons'] = array(
            'refresh'=>array(
                'type'=>'submit',
                'label'=>Yii::t('cms', 'Save'),
                'title'=>Yii::t('cms', 'Save and reload the page'),
            ),
        );
        $form_array['activeForm'] = Form::ajaxify($form_array['id']);
		if ($page->id != 1) {
			$form_array['buttons']['deletepage'] = array(
				'type'=>'submit',
				'label'=>Yii::t('cms', 'Delete'),
				'title'=>Yii::t('cms', 'Delete page'),
			);
		}
		$form = new Form($form_array);
		$form->model = $page;

        $this->performAjaxValidation($page);
		
		if ($form->submitted('save')||$form->submitted('refresh')) {
			$page = $form->model;
			if ($form->validate()) {
				//$page->path = '';
				if ($page->save(false))
                    Yii::app()->user->setFlash('save', Yii::t('cms', 'Properties has been saved successfully'));
                else
                    Yii::app()->user->setFlash('save-error-permanent', Yii::t('cms', 'There is some error on page saving'));
			}
		} elseif ($form->submitted('delete')) {
			$page = $form->model;
			if ($page->id != 1)
			{
				$parentId = $page->parent_id ? $page->parent_id : 1;
                $params = array('pageId'=>$parentId);
                if ($page->parent_id) {
                    $params['url'] = $page->parent->url;
                    $params['alias'] = $page->parent->url;
                }
				echo CJavaScript::jsonEncode(array('url' => $this->createAbsoluteUrl('view/index', $params)));
				$page->delete();
                Yii::app()->user->setFlash('delete', Yii::t('cms', 'Page deleted.'));
				Yii::app()->end();
			}
		}

		$form = new Form($form_array);
		$form->model = $page;
        $caption = array(
            'icon' => 'edit',
            'label' => Yii::t('cms', 'Page properties'),
        );

        $this->render('/form', array('form'=>$form, 'caption'=>$caption));
	}
	
    /**
     * Удаляет страницу
     *
     * @param int $pageId id страницы
     */
	public function actionDelete($pageId)
	{
		$page = Yii::app()->page->model;
		$pageId = 1;
		if ($_REQUEST['deletechildren'])
		{
			$pageId = $page->parent_id ? $page->parent_id : 1;
			$page->deleteWithChildren();
		}
		elseif ($_REQUEST['movechildren'] && $_REQUEST['newParent'])
		{
			$pageId = $_REQUEST['newParent'];
			$children = $page->children;
			if ($children)
				foreach ($children as $child)
				{
					$child->parent_id = $pageId;
					$child->save(false);
				}
			$page->delete();
		}
        $page = Page::model()->findByPk($pageId);
		echo CJavaScript::jsonEncode(array('url' => $this->createAbsoluteUrl('view/index', array('pageId'=>$page->id, 'alias'=>$page->alias, 'url'=>$page->url))));
		Yii::app()->end();
	}

    /**
     * Обрабатывает перемещение страниц
     *
     * @param int $pageId id страницы
     */
	public function actionSort($pageId)
	{
		$transaction=Yii::app()->db->beginTransaction();
		try
		{
			if ($pageId > 1 &&
				$_REQUEST['parentId'] && ($_REQUEST['parentId'] != 0) &&
				isset($_REQUEST['order']))
			{
				$page = Yii::app()->page->model;
				$page->parent_id = $_REQUEST['parentId'];
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

    /**
     * Отображает дерево страниц
     */
	public function actionTree()
	{
		if (isset($_GET['pageId']) && !empty($_GET['pageId'])) {
			$model = Yii::app()->page->model;
			if (!$_REQUEST['treeId']) {
				$_REQUEST['treeId'] = 'pagetree'.$_GET['pageId'];
			}
			$tree = Page::model()->getTree($model->id);
		} else {
 			if (!$_REQUEST['treeId']) {
				$_REQUEST['treeId'] = 'pagetree';
			}
			$tree = Page::model()->getTree();
		}
		$this->render('tree', array('tree' => $tree, 'treeId' => $_REQUEST['treeId'],
										'multiple' => (bool)$_REQUEST['multiple'],
										'enabledOnly' => $_REQUEST['enabledOnly'],
										'disabled' => $_REQUEST['disabled']));
	}
	
    /**
     * Отображает диалог для заполнения пустой страницы
     */
	public function actionFill()
	{
		$this->render('fill');
	}
	
    /**
     * Отображает уточнение при удалении страницы
     *
     * @param int $pageId id страницы
     */
	public function actionDeleteDialog($pageId)
	{
		$this->render('deleteDialog',array(
			'model'=>Yii::app()->page->model
		));
		
	}

    /**
     * Возвращает ссылку по id страницы
     *
     * @param int $pageId id страницы
     */
	public function actionGetUrl($pageId)
	{
        $model = Yii::app()->page->model;
		echo $this->createAbsoluteUrl('view/index', array('pageId'=>$model->id, 'alias'=>$model->alias, 'url'=>$model->url));
	}

	//
    /**
     * Возвращает количество дочерних страниц
     *
     * @param int $pageId id страницы
     */
	public function actionHasChildren($pageId)
	{
        $pageId = (int)$pageId;
        echo Page::model()->count('parent_id = :parent_id',array(':parent_id'=>$pageId));
	}

	/**
     * Исполняет проверку формы
     *
     * @param CActiveRecord $model
     * @param array $attributes
     * @param boolean $loadInput
     */
    protected function performAjaxValidation($model, $attributes=null, $loadInput=true)
	{
		if(isset($_REQUEST['ajax-validate']))
		{
            if (!$_REQUEST['delete'] && !$_REQUEST['deletepage']) {
                echo CActiveForm::validate($model, $attributes, $loadInput);
            }
			Yii::app()->end();
		}
	}

}
