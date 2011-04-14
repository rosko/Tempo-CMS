<?php

class PageController extends Controller
{
	public $_model;
	public $defaultAction = 'view';

	public function filters()
	{
		return array(
			'accessControl',
/*            array(
                'COutputCache + view',
                'duration'=>3600,
                'id'=>serialize(Page::cacheVaryBy()),
                'varyByParam'=>array('id','language','alias','url'),
                'dependency'=>array(
                    'class'=> 'CChainedCacheDependency',
                    'dependencies'=>array(
                        new CFileCacheDependency(Unit::configFilename()),
                        new CDbCacheDependency('SELECT CONCAT(MAX(`create`),MAX(`modify`)) FROM `'.Unit::tableName().'`'),
                    )
                ),
            ),*/
		);
	}

	public function accessRules()
	{
        if (!isset($_GET['id']) && !isset($_GET['page_id'])) $this->paramId();
		$ret = array(
            array('allow',
                'actions'=>array(
                    'pageAdd',
                ),
                'expression'=>'Yii::app()->user->checkAccess("createPage", array("page"=>Yii::app()->controller->loadModel()))',
            ),
			array('allow',
				'actions'=>array(
                    'view', 'unitView', 'jsI18N',
                    'unitAjax',
                    'pageTree',
                    'getUrl',
                ),
                'expression'=>'Yii::app()->user->checkAccess("readPage", array("page"=>Yii::app()->controller->loadModel()))',
			),
			array('allow',
				'actions'=>array(
                    'getPageunitsByUnit',
                    'unitAdd', 'unitForm',
                    'unitSetDialog', 'unitSet', 'unitMove',
                    'unitDeleteDialog','unitDelete', 'unitCheck',

                    'pageForm',
                    'pageRename', 'pageFill', 'pagesSort',
                ),
                'expression'=>'Yii::app()->user->checkAccess("updatePage", array("page"=>Yii::app()->controller->loadModel()))',
			),
			array('allow',
				'actions'=>array(
                    'unitAdd', 'unitForm',
                ),
                'expression'=>'Yii::app()->user->checkAccess("updateUser", array("page"=>Yii::app()->controller->loadModel()))',
			),
            array('allow',
                'actions'=>array(
                    'siteMap',
                ),
                'expression'=>'Yii::app()->user->checkAccess("createPage", array("page"=>Yii::app()->controller->loadModel())) ||
                              Yii::app()->user->checkAccess("updatePage", array("page"=>Yii::app()->controller->loadModel())) ||
                              Yii::app()->user->checkAccess("deletePage", array("page"=>Yii::app()->controller->loadModel()))',
            ),
            array('allow',
                'actions'=>array(
                    'hasChildren',
                    'pageDeleteDialog', 'pageDelete',
                ),
                'expression'=>'Yii::app()->user->checkAccess("deletePage", array("page"=>Yii::app()->controller->loadModel()))',
            ),
			array('allow',
				'actions'=>array(
                    'siteSettings',
                ),
                'expression'=>'Yii::app()->user->checkAccess("updateSettings", array("page"=>Yii::app()->controller->loadModel()))',
			),
			array('allow',
				'actions'=>array(
                    'unitsInstall',
                ),
                'expression'=>'Yii::app()->user->checkAccess("manageUnit", array("page"=>Yii::app()->controller->loadModel()))',
			),
			array('deny',
				'users'=>array('*'),
			),
		);
        $actions = array();
        foreach ($ret as $r) {
            if (is_array($r['actions']))
                $actions = array_merge($actions, $r['actions']);
        }
        $ret = CMap::mergeArray(array(array(
            'allow',
            'actions'=>$actions,
            'users'=>array('admin'),
        )), $ret);
        return $ret;
	}

    protected function paramId()
    {
        // Поиск страницы
        $lang = Yii::app()->language;
        if (!empty($_GET['alias'])){
            $page = Page::model()->getAll("`{$lang}_alias` = :alias", array(':alias'=> $_GET['alias']));
            if (isset($page[0]['id']) && (!Yii::app()->params['strictFind'] || $page[0][$lang.'_alias']==$_GET['alias']))
                $_GET['id'] = $page[0]['id'];
        } elseif (!empty($_GET['url'])) {
            $page = Page::model()->getAll("`{$lang}_url` = :url", array(':url'=> '/'.$_GET['url']));
            if (isset($page[0]['id']) && (!Yii::app()->params['strictFind'] || $page[0][$lang.'_url']=='/'.$_GET['url']))
                $_GET['id'] = $page[0]['id'];
        } else $_GET['id'] = 1;
    }

	// Отображает страницу
	public function actionView()
	{
/*
 * TODO: Доделать перемещение блоков, бывают некритические ошибки
        $ret = PageUnit::checkIntegrity();
        if ($ret['percents'] > 0) {
            echo '<pre>';
            print_r ($ret);
            echo '</pre>';
        }
 */
		if (!isset($_GET['id'])) {
            $this->paramId();
		} else {
            // Сделать переадрессацию, если страница запрошена по id без указания адреса
            // и при этом режим редактирования отключен
            if (!Yii::app()->user->checkAccess('updatePage', array('page'=>$this->loadModel())) &&
                (Yii::app()->getUrlManager()->getUrlFormat()==UrlManager::PATH_FORMAT) &&
                !$_GET['alias'] && !$_GET['url']) {
                $page = Page::model()->findByPk(intval($_GET['id']));
                if (Yii::app()->getUrlManager()->fullUrl && $page->url) {
                    $this->redirect($this->createAbsoluteUrl('page/view',array(
                        'url'=>$page->url,
                    )));
                } elseif (!Yii::app()->getUrlManager()->fullUrl && $page->alias) {
                    $this->redirect($this->createAbsoluteUrl('page/view',array(
                        'alias'=>$page->alias,
                        'id'=>$page->id,
                    )));
                }
            }
        }                
        if ($this->loadModel()->redirect) {
            if (!Yii::app()->user->checkAccess('updatePage', array('page'=>$this->loadModel())))
                $this->redirect($this->loadModel()->redirect);
            else
                Yii::app()->user->setFlash('redirect-permanent-hint', Yii::t('cms', 'This page has redirection to') . '<a href="'.$this->loadModel()->redirect . '">'.$this->loadModel()->redirect.'</a>. <a class="ui-button-icon" href="" onclick="$(\'#toolbar_edit\').click();return false;">'.Yii::t('cms', 'Page properties').'</a>');
        }

        $unitContent = '';
        if (isset($_GET['i'])) {
            $className = Unit::getClassNameByUnitType($_GET['i']);
            if (class_exists($className) && method_exists($className, 'content')) {
                $unitContent = call_user_func(array($className, 'content'));
            }
        }

		$this->render('view',array(
			'model'=>$this->loadModel(),
            'unitContent' => $unitContent,
		));
        Yii::app()->user->getFlashes(true);
	}

	// Создает новую страницу
	public function actionPageAdd()
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
			'label'=>Yii::t('cms', 'Save & Go'),
			'title'=>Yii::t('cms', 'Save & Go to the new page'),
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
                        $url = $this->createAbsoluteUrl('page/view', array('id'=>$page->id, 'alias'=>$page->{$alias_param}, 'url'=>$page->generateUrl(true,$alias_param)));
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
        if (isset($_REQUEST['json']) && $_REQUEST['json']) {
            echo CJavaScript::jsonEncode(array(
                'unique_id'=> 'yform_'.$form->id,
                'underscore' => $_REQUEST['_'],
            ));
            Yii::app()->end();
        }
        $caption = array(
            'icon' => Toolbar::getIconUrlByAlias('add', '', 'fatcow', '32x32'),
            'label' => Yii::t('cms', 'New page'),
        );
        $this->render('form', array('form'=>$form, 'caption' => $caption));
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
				$parent_id = $page->parent_id ? $page->parent_id : 1;
                $params = array('id'=>$parent_id);
                if ($page->parent_id) {
                    $params['url'] = $page->parent->url;
                    $params['alias'] = $page->parent->url;
                }
				echo CJavaScript::jsonEncode(array('url' => $this->createAbsoluteUrl('page/view', $params)));
				$page->delete();
                Yii::app()->user->setFlash('delete', Yii::t('cms', 'Page deleted.'));
				Yii::app()->end();
			}
		}

		$form = new Form($form_array);
		$form->model = $page;
        $caption = array(
            'icon' => Toolbar::getIconUrlByAlias('edit', '', 'fatcow', '32x32'),
            'label' => Yii::t('cms', 'Page properties'),
        );

        $this->render('form', array('form'=>$form, 'caption'=>$caption));
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
        $page = Page::model()->findByPk($page_id);
		echo CJavaScript::jsonEncode(array('url' => $this->createAbsoluteUrl('page/view', array('id'=>$page->id, 'alias'=>$page->alias, 'url'=>$page->url))));
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

    /**
     * Обрабатывает перемещение блоков по странице
     */
	public function actionUnitMove()
	{
        /*
         * $_REQUEST['area'] - название области блоков
         * $_REQUEST['cms-pageunit'] - массив id блоков в порядке их размещения в области $_REQUEST['area']
         * $_REQUEST['pageunit_id'] - id блока, который перемещается
         */
        if (isset($_REQUEST['pageunit_id']) && isset($_REQUEST['area']) && isset($_REQUEST['cms-pageunit']) && is_array($_REQUEST['cms-pageunit']))
        {
            $unit = Unit::model()->findByPk(PageUnit::getUnitIdById($_REQUEST['pageunit_id']));
            echo $unit->move($_REQUEST['area'], $_REQUEST['cms-pageunit'], $_REQUEST['pageunit_id']);
        }
	}
	
	// Отображает юнит
	public function actionUnitView()
	{
		$pageunit = PageUnit::model()->with('unit')->findByPk($_REQUEST['pageunit_id']);
        if ($pageunit) {
            $className = Unit::getClassNameByUnitType($pageunit->unit->type);
            $pageunit->unit->content->run(array(
                'pageunit'=>$pageunit
            ));
        }
	}

    public function actionUnitAjax($unit_id)
    {
        $unit = Unit::model()->findByPk($unit_id);
        $unit->content->ajax($_REQUEST);
    }
	
    /*
     * Создает новый юнит
     * $_REQUEST['pageunit_id'] - id блока на странице после которого размещается новый блок
     * $_REQUEST['area'] - область, где размещается новый блок
     * $_REQUEST['type'] - тип юнита
     * $_REQUEST['page_id'] - id страницы
     */
	public function actionUnitAdd()
	{
		if (isset($_REQUEST['pageunit_id']) && isset($_REQUEST['area']) && isset($_REQUEST['type']) && isset($_REQUEST['page_id']))
		{
            // Создаем юнит
            $langs = array_keys(I18nActiveRecord::getLangs(Yii::app()->language));
			$unit = new Unit;
			$unit->type = $_REQUEST['type'];
            $className = Unit::getClassNameByUnitType($_REQUEST['type']);
			$unit->title = call_user_func(array($className, 'name'));
            foreach ($langs as $lang) {
                $unit->{$lang.'_title'} = call_user_func(array($className, 'name'), $lang);
            }
			$unit->save();

			// Размещаем его на только текущей странице
            if ($_REQUEST['pageunit_id']) {
                $pu = PageUnit::model()->findByPk($_REQUEST['pageunit_id']);
                $order = $pu->order;
            } else {
                $order = -1;
            }
            $pageunit = $unit->setOnPage($_REQUEST['page_id'], $_REQUEST['area'], $order);
            
			// Заполняем юнит информацией по-умолчанию
			if (method_exists($className, 'defaultObject')) {
				$content = call_user_func(array($className, 'defaultObject'));
			} else {
				$content = new $className;
			}
			$content->unit_id = $unit->id;
            if (isset($_REQUEST['content_page_id']) && $content->hasAttribute('page_id')) {
                $page = Page::model()->findByPk(intval($_REQUEST['content_page_id']));
                if ($page) {
                    $page->virtual = true;
                    $page->title = $unit->title;
                    foreach ($langs as $lang) {
                        $page->{$lang.'_title'} = $unit->{$lang.'_title'};
                    }
                    $page->save(false);
                    $content->page_id = intval($_REQUEST['content_page_id']);
                }
            }
            if (isset($_REQUEST['section_id']) && isset($_REQUEST['foreign_attribute'])
                && $content->hasAttribute($_REQUEST['foreign_attribute'])    ) {
                $content->{$_REQUEST['foreign_attribute']} = intval($_REQUEST['section_id']);
            }
			$content->save(false);
			$unit->save();
			
            echo CJavaScript::jsonEncode(array('pageunit_id'=>$pageunit->id,'unit_id'=>$unit->id,'content_id'=>$content->id));
		}
        else echo '0';
	}
	
	// Редактирует свойства юнита
	public function actionUnitForm()
	{
        if ($_REQUEST['unit_type']) {
            $unit_class = Unit::getClassNameByUnitType($_REQUEST['unit_type']);
            if ($_REQUEST['pageunit_id']) {
                $pageunit = PageUnit::model()->with('unit')->findByPk($_REQUEST['pageunit_id']);
                $unit = $pageunit->unit;
                $content = $unit->content;
            } elseif ($_REQUEST['unit_id']) {
                $unit = Unit::model()->findByPk($_REQUEST['unit_id']);
                $content = $unit->content;
            } else {
                $unit = new Unit;
                $content = new $unit_class;
            }
            $content->scenario = 'edit';
        } elseif ($_REQUEST['class_name']) {
            $unit_class = $_REQUEST['class_name'];
            if ($_REQUEST['record_id']) {
                $content = call_user_func(array($unit_class, 'model'))->findByPk($_REQUEST['record_id']);
                $content->scenario = 'edit';
            } elseif (method_exists($unit_class, 'defaultObject')) {
				$content = call_user_func(array($nit_class, 'defaultObject'));
			} else {
				$content = new $unit_class;
                $content->scenario = 'add';
			}

            if (!empty($content->unit_id)) {
                $unit = $content->unit;
            }
            
        } else return false;
        $id = $unit_class.$unit->id;

        $unit_form_array = call_user_func(array($unit_class, 'form'));
		$unit_form_array['type'] = 'form';
        $unit_form_array['id'] = $id;
        $show_title = true;
        if (isset($unit_form_array['title'])) {
            if ($unit_form_array['title'] === false)
                $show_title = false;
            unset($unit_form_array['title']);
        }
        //  В окно редактирования свойств подключаем диалог для управления размещением
//        if (isset($pageunit)) {
//            $unit_form_array['elements'][] = Form::tab('Размещение', '/?r=page/unitSetDialog&id='.$pageunit->page_id.'&unit_id='.$pageunit->unit_id.'&pageunit_id='.$pageunit->id);
//        }
		$form_array = array(
            'id' => $id,
            'activeForm' => Form::ajaxify($id),
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
        if (substr($unit_form_array['elements'][0],0,2)!=Form::TAB_DELIMETER
                || substr($unit_form_array['elements'][0],-2)!=Form::TAB_DELIMETER)
        {
            $form_array['title']='';
        }
        if (method_exists($unit_class, 'name')) {
            if (is_subclass_of($unit_class, 'Content')) {
                $caption = array(
                    'icon' => str_replace('16x16', '32x32', constant($unit_class.'::ICON')),
                    'label' => call_user_func(array($unit_class, 'name')),
                );
            } else {
                $caption = array(
                    'icon' => str_replace('16x16', '32x32', constant($unit_class.'::ICON')),
                    'label' => $content->name(),
                );
            }
        } else {
        }
        if (isset($unit)) {
            $form_array['elements']['unit'] = array(
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
                $form_array['elements']['unit']['elements']['template'] = array(
                    'type'=>'TemplateSelect',
                    'className'=>$unit_class,
                    'empty'=>Yii::t('cms', '«accordingly to general settings»'),
                );
                $unit_form_array['elements'][] = Form::tab(Yii::t('cms', 'Appearance'));
            }
        }
        $form_array['elements']['content'] = $unit_form_array;

		$form = new Form($form_array);
        if (isset($unit))
            $form['unit']->model = $unit;
		$form['content']->model = $content;

        if (isset($unit)) {
            $this->performAjaxValidation(array($unit, $content));
        } else
            $this->performAjaxValidation($content);

		if ($form->submitted('save') || $form->submitted('apply')) {
            if (isset($unit))
                $unit = $form['unit']->model;
			$content = $form['content']->model;
			if ($form->validate()) {
                if (isset($unit)) {
    				$content->unit_id = $unit->id;
            		if ($unit->save(false)) {
                		$content->save(false);
                        if ($content->hasAttribute('page_id')) {
                            $p = Page::model()->findByPk($content->page_id);
                            if ($p) {
                                $p->title = $unit->title;
                                $langs = array_keys(I18nActiveRecord::getLangs(Yii::app()->language));
                                foreach ($langs as $lang) {
                                    $param = $lang.'_title';
                                    $p->$param = $unit->$param;
                                }
                                $p->save(false);
                            }
                        }
                    }
                } else $content->save(false);
			}
		}
		$form = new Form($form_array);
        if (isset($unit))
            $form['unit']->model = $unit;
		$form['content']->model = $content;
		
		$this->render('form', array('form'=>$form, 'show_title'=>$show_title, 'caption'=>$caption));
	}
	
	// Удаляет юнит
	public function actionUnitDelete()
	{
        if (isset($_REQUEST['unit_id']) && isset($_REQUEST['pageunit_id']))
		{

            if ($_REQUEST['pageunit_id'] == 'all') {
                $sql = 'UPDATE `' . PageUnit::tableName() . '` as pu
                        INNER JOIN (SELECT `order`, `area`, `page_id` FROM `' . PageUnit::tableName() . '`
                                    WHERE `unit_id` = :unit_id) as pu2
                        ON pu.`page_id` = pu2.`page_id`
                        SET pu.`order` = pu.`order`-1
                        WHERE
                            pu.`area` = pu2.`area`
                            AND pu.`order` > pu2.`order`';
                $command = Yii::app()->db->createCommand($sql);
                $command->bindValue(':unit_id', $_REQUEST['unit_id'], PDO::PARAM_INT);
                $command->execute();
                PageUnit::model()->deleteAll('unit_id = :unit_id', array(':unit_id' => $_REQUEST['unit_id']));
            } elseif (is_array($_REQUEST['pageunit_id'])) {
                $sql = 'UPDATE `' . PageUnit::tableName() . '` as pu
                        INNER JOIN (SELECT `order`, `area`, `page_id` FROM `' . PageUnit::tableName() . '`
                                    WHERE `id` IN ("'.implode('","',$_REQUEST['pageunit_id']).'")
                                        AND `unit_id` = :unit_id) as pu2
                        ON pu.`page_id` = pu2.`page_id`
                        SET pu.`order` = pu.`order`-1
                        WHERE
                            pu.`area` = pu2.`area`
                            AND pu.`order` > pu2.`order`';
                $command = Yii::app()->db->createCommand($sql);
                $command->bindValue(':unit_id', $_REQUEST['unit_id'], PDO::PARAM_INT);
                $command->execute();
                PageUnit::model()->deleteAll('`id` IN ("'.implode('","',$_REQUEST['pageunit_id']).'")');
            }
			
			$c = PageUnit::model()->count('unit_id = :unit_id', array(':unit_id' => $_REQUEST['unit_id']));
			if ($c == 0)
			{
                $unit = Unit::model()->findByPk($_REQUEST['unit_id']);
				if ($unit) {
                    // Если нужно, также удаляем ассоциированную страницу
                    if ($_REQUEST['with_page'] && $unit->content && $unit->content->hasAttribute('page_id'))
                    {
                        $p = Page::model()->findByPk($unit->content->page_id);
                        if ($p)
                            $p->delete();
                    }
                    $unit->delete();
                }
			}
			echo '1';
        } else
            echo '0';
    }

    public function actionUnitCheck()
    {
        $ret = array();
        if ($_REQUEST['unit_id']) {
            $unit = Unit::model()->findByPk($_REQUEST['unit_id']);
            // Связана ли какая-то страница с этим юнитом?
            if ($unit->content && $unit->content->hasAttribute('page_id')) {
                $p = Page::model()->findByPk($unit->content->page_id);
                $pu = PageUnit::model()->find('unit_id = :unit_id AND page_id = :page_id',
                                        array(':unit_id'=>$_REQUEST['unit_id'], ':page_id'=>$unit->content->page_id));
                if ($p && $pu) {
                    $ret['page'] = array(
                        'title' => $p->title,
                        'url' => $this->createAbsoluteUrl('view', array('id'=>$p->id, 'alias'=>$p->alias, 'url'=>$p->url)),
                        'similarToParent' => $p->isSimilarTo($p->parent_id, 'all', $unit->id),
                    );
                }
            }
        }
        echo CJavaScript::jsonEncode($ret);
    }
	
	// Редактирует свойства сайта
	public function actionSiteSettings()
	{
		//$settingsForm = ;
		$form_array = SiteSettingsForm::form();
//		$form_array['title'] = Yii::t('cms', 'General settings');
        $form_array['id'] = 'SiteSettings';
        $form_array['activeForm'] = Form::ajaxify($form_array['id']);
		$form_array['buttons'] = array(
				'refresh'=>array(
					'type'=>'submit',
					'label'=>Yii::t('cms', 'Save'),
					'title'=>Yii::t('cms', 'Save and reload the page'),
				),
			);
		$form = new Form($form_array);
        $form->id = $form_array['id'];
		$form->model = clone Yii::app()->settings->model;

        $form->loadData();
        $this->performAjaxValidation($form->model, null, false);

		if ($form->submitted('refresh')) {
            if ($form->model->validate()) {
                Yii::app()->settings->saveAll($form->model->getAttributes());
                Yii::app()->installer->installAll(false);
                echo '1';
            } else {
                echo '0';
            }
            Yii::app()->end();
		}

        $caption = array(
            'icon' => Toolbar::getIconUrlByAlias('settings', '', 'fatcow', '32x32'),
            'label' => Yii::t('cms', 'Site settings'),
        );
		$this->render('form', array('form'=>$form, 'caption'=>$caption));
		
	}
	
	// Отображает карту сайта
	public function actionSiteMap()
	{
		$tree = Page::model()->getTree();
		$initially_open = array();
		$opened_levels = Yii::app()->request->isAjaxRequest ? 1 : 2;
		foreach ($tree as $pages)
		{
			foreach ($pages as $p)
			{
				if (substr_count($p['path'],',') < $opened_levels)
					$initially_open[] = 'page-'.$p['id'];
			}
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
		$this->render('pagetree', array('tree' => $tree, 'tree_id' => $_REQUEST['tree_id'],
										'multiple' => (bool)$_REQUEST['multiple'],
										'enabledOnly' => $_REQUEST['enabledOnly'],
										'disabled' => $_REQUEST['disabled']));
	}
	
	// Отображает диалог для заполнения пустой страницы
	public function actionPageFill()
	{
		$this->render('fill');
	}
	
	// Отображает уточнение при удалении страницы
	public function actionPageDeleteDialog()
	{
		$this->render('pageDeleteDialog',array(
			'model'=>$this->loadModel()
		));
		
	}

    // Обрабатывает размещение юнита на нескольких страницах
	public function actionUnitSet()
	{
		if (isset($_REQUEST['unit_id']) && isset($_REQUEST['pageunit_id']) && isset($_REQUEST['page_ids']))
		{
            $unit = Unit::model()->findByPk(intval($_REQUEST['unit_id']));
            echo (int)$unit->setOnPagesOnly($_REQUEST['page_ids'], $_REQUEST['pageunit_id']);
        } else
            echo '0';
	}

    // Отображает диалог выбора страниц где будет размещен юнит
    public function actionUnitSetDialog()
	{
		$this->render('unitSetDialog',array(
			'model'=>$this->loadModel(),
			'unit'=>Unit::model()->findByPk($_REQUEST['unit_id']),
			'unit_id'=>intval($_REQUEST['unit_id']),
			'pageunit_id'=>intval($_REQUEST['pageunit_id'])
		));
	}

    // Отображает уточнение при удалении юнита
	public function actionUnitDeleteDialog()
	{
		$this->render('unitDeleteDialog',array(
			'model'=>$this->loadModel(),
			'unit'=>Unit::model()->findByPk($_REQUEST['unit_id']),
			'unit_id'=>$_REQUEST['unit_id'],
			'pageunit_id'=>$_REQUEST['pageunit_id']
		));		
	}

    // Возвращает ссылку по id страницы
	public function actionGetUrl()
	{
        $model = $this->loadModel();
		echo $this->createAbsoluteUrl('page/view', array('id'=>$model->id, 'alias'=>$model->alias, 'url'=>$model->url));
	}

	// Возвращает размещения юнитов по юниту
	public function actionGetPageunitsByUnit()
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

    public function actionJsI18N($language)
    {
        header('Content-type: text/javascript');
        Yii::app()->language = $language;
        $this->renderPartial('jsI18N');
    }

    public function actionUnitsInstall()
    {
        $all_units = Unit::getAllUnits();
        $errors = array();
        if (isset($_POST['Units'])) {
            $units = array_keys($_POST['Units']);
            Unit::install($units);
            $uninstall = array_diff(array_keys($all_units), $units);
            foreach ($uninstall as $i=>$className) {
                $sql = 'SELECT count(*) FROM `' . Unit::tableName() . '` WHERE `type` = :type';
                $command = Yii::app()->db->createCommand($sql);
                $command->bindValue(':type', Unit::getUnitTypeByClassName($className), PDO::PARAM_STR);
                $exists = $command->queryScalar();
                if ($exists) {
                    unset($uninstall[$i]);
                    $errors[] = Yii::t('cms', 'Can\`t unistall "{name}"', array('{name}'=>$all_units[$className]['name']));
                }
            }
            Unit::uninstall($uninstall);
            $all_units = Unit::getAllUnits();
        }
        
        $this->render('unitsInstall', array(
            'units' => $all_units,
            'errors' => $errors,
        ));
    }

	public function loadModel()
	{
		if($this->_model===null)
		{
			if(isset($_GET['id']))
				$this->_model=Page::model()->findbyPk($_GET['id']);
			elseif(isset($_GET['page_id']))
				$this->_model=Page::model()->findbyPk($_GET['page_id']);
			if($this->_model===null)
				throw new CHttpException(404,Yii::t('cms', 'The requested page does not exist.'));
            else {
                if ($this->_model->language)
                    Yii::app()->language = $this->_model->language;
            }
		}
		return $this->_model;
	}

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
