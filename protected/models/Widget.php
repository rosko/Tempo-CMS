<?php

class Widget extends I18nActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'widgets';
	}

    public function scheme()
    {
        return array(
            'class' => 'char(64)',
            'title' => 'string',
            'template' => 'text',
            'author_id'=>'integer unsigned',
            'editor_id'=>'integer unsigned',
            'access'=>'text',
        );
    }

	public function rules()
	{
		return $this->localizedRules(array(
			array('class', 'required'),
			array('class', 'length', 'max'=>64),
			array('title', 'length', 'max'=>255),
			array('template', 'safe'),
		));
	}

    public function i18n()
    {
        return array('title');
    }

	public function relations()
	{
		return array(
			'pages' => array(self::MANY_MANY, 'Page', Yii::app()->db->tablePrefix.'pages_widgets(widget_id,page_id)')
		);
	}

    public function  behaviors() {
        return array(
            'CTimestampBehavior' => array(
                'class' => 'zii.behaviors.CTimestampBehavior',
                'createAttribute' => 'create',
                'updateAttribute' => 'modify',
            ),
            'CSerializeBehavior' => array(
                'class' => 'application.behaviors.CSerializeBehavior',
                'serialAttributes' => array('template'),
            ),
        );
    }

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
//			'class' => Yii::t('cms', 'Type),
			'title' => Yii::t('cms', 'Title'),
            'template' => Yii::t('cms', 'Template'),
		);
	}

    public function operations()
    {
        return array(
            'create'=>array(
                'label'=>'Create widget', // Право создавать блоки
                'defaultRoles'=>array('author', 'editor', 'administrator'),
            ),
            'read'=>array(
                'label'=>'View widget', // Право просматривать блоки
                'defaultRoles'=>array('anybody'),
            ),
            'update'=>array(
                'label'=>'Update widget', // Право редактировать блоки
                'defaultRoles'=>array('editor' , 'administrator'),
            ),
            'updateAccess'=>array(
                'label'=>'Update widget access', // Право редактировать права доступа к блокам
                'defaultRoles'=>array('administrator'),
            ),
            'move'=>array(
                'label'=>'Move widget', // Право перемещать блоки
                'defaultRoles'=>array('administrator', 'editor'),
            ),
            'delete'=>array(
                'label'=>'Delete widget', // Право удалять блоки
                'defaultRoles'=>array('editor', 'administrator'),
            ),
            'manage'=>array(
                'label'=>'Move widget', // Право инсталлировать/деинсталлировать юниты
                'defaultRoles'=>array('administrator'),
            ),
        );
    }

    public function tasks()
    {
        return array(
            'readOwn'=>array(
                'label'=>'View own widget',
                'bizRule'=>'return Yii::app()->user->id==$params["widget"]->author_id;',
                'children'=>array('readWidget'),
                'defaultRoles'=>array('author', 'authenticated'),
            ),
            'updateOwn'=>array(
                'label'=>'Edit own widget',
                'bizRule'=>'return Yii::app()->user->id==$params["widget"]->author_id;',
                'children'=>array('updateWidget'),
                'defaultRoles'=>array('author', 'authenticated'),
            ),
            'updateAccessOwn'=>array(
                'label'=>'Edit own widget access',
                'bizRule'=>'return Yii::app()->user->id==$params["widget"]->author_id;',
                'children'=>array('updateAccessWidget'),
                'defaultRoles'=>array('author'),
            ),
            'deleteOwn'=>array(
                'label'=>'Delete own widget',
                'bizRule'=>'return Yii::app()->user->id==$params["widget"]->author_id;',
                'children'=>array('deleteWidget'),
                'defaultRoles'=>array('author'),
            ),
        );
    }

	/**
     * Возвращает объект содержащий контент блока
     * 
     * @return mixed объект содержащий контент блока
     */
    public function getContent()
	{
        $widgetClass = $this->class;
        $modelClass = call_user_func(array($widgetClass, 'modelClassName'));
		return call_user_func(array($modelClass, 'model'))->find('widget_id=:id', array(':id'=>$this->id));
	}

    /**
     * Находит одну страницу где размещен экземпляр юнита
     *
     * @return array свойства страницы
     */
    public function getWidgetPageArray()
    {
        $sql = 'SELECT p.* FROM `'.Page::tableName().'`  as p INNER JOIN `' . PageWidget::tableName() . '` as pu ON pu.page_id = p.id WHERE pu.widget_id = :widget_id ORDER BY pu.id LIMIT 1';
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(':widget_id', $this->id, PDO::PARAM_INT);
        $page = $command->queryRow();
        $page['alias'] = $page[Yii::app()->language.'_alias'];
        $page['url'] = $page[Yii::app()->language.'_url'];
        return $page;
    }

    /**
     * Возвращает ссылку на одну страницу где размещен экземпляр юнита
     * 
     * @param bool $absolute указывает в каком виде нужно вернуть ссылку 
     * (true - абсолютная ссылка, false - относительная)
     * @param array $params дополнительные параметры для ссылки
     * @return string ссылка 
     */
    public function getWidgetUrl($absolute=false, $params=array())
    {
        $page = $this->getWidgetPageArray();
        $params = array_merge(array('pageId'=>$page['id'], 'alias'=>$page['alias'], 'url'=>$page['url']), $params);
        if ($absolute)
            return Yii::app()->controller->createAbsoluteUrl('view/index', $params);
        else
            return Yii::app()->controller->createUrl('view/index', $params);
    }

	public function beforeDelete()
	{
		$this->content->delete();
        return parent::beforeDelete();
	}

    /**
     * Устанавливает юнит на конкретной странице в определенном месте
     * 
     * @param integer pageId id страницы
     * @param string название области блоков
     * @param integer номер по порядку размещения блоков
     * @return PageWidget
     */
    public function setOnPage($pageId, $area, $order)
    {
        $pageId = (int)$pageId;
        // Раздвигаем последующие юниты
        $sql = 'UPDATE `' . PageWidget::tableName() . '` SET `order`=`order`+1 WHERE `page_id` = :page_id AND `area` = :area AND `order` > :order';
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(':page_id', $pageId, PDO::PARAM_INT);
        $command->bindValue(':area', $area, PDO::PARAM_STR);
        $command->bindValue(':order', $order, PDO::PARAM_INT);
        $command->execute();

        // Устанавливаем юнит
        $pageWidget = new PageWidget;
        $pageWidget->page_id = $pageId;
        $pageWidget->widget_id = $this->id;
        $pageWidget->order = $order+1;
        $pageWidget->area = $area;
        $pageWidget->save();

        return $pageWidget;
    }

    /**
     * Устанавливает юнит только на конкретных страницах
     * @param array id страниц, где должен быть размещен юнит
     * @param integer id pageWidget'а, на основе которого делается размещение на других страницах
     * @return boolean true в случае удачной операции, false - в обратном случае
     */
    public function setOnPagesOnly($pageIds, $pageWidgetId)
    {
        $transaction=Yii::app()->db->beginTransaction();
		try
		{
            $pageWidget = PageWidget::model()->findByPk($pageWidgetId);
            if ($pageWidget) {
                if (empty($pageIds)) {
                    $pageIds = array($pageWidget->page_id);
                }

                $sql = 'SELECT `page_id` FROM `' . PageWidget::tableName() . '` WHERE widget_id = :widget_id';
                $command = Yii::app()->db->createCommand($sql);
                $command->bindValue(':widget_id', $this->id, PDO::PARAM_INT);
                $curPageIds = $command->queryColumn();

                // Удаляем лишние pageWidget`ы
                $delPageIds = array_diff($curPageIds, $pageIds);
                if (!empty($delPageIds)) {
                    $sql = 'DELETE FROM `' . PageWidget::tableName() . '` WHERE widget_id = :widget_id AND `page_id` IN (' . implode(', ',$delPageIds) . ')';
                    $command = Yii::app()->db->createCommand($sql);
                    $command->bindValue(':widget_id', $this->id, PDO::PARAM_INT);
                    $command->execute();

                    $sql = 'UPDATE `' . PageWidget::tableName() . '` SET `order`=`order`-1 WHERE `page_id` IN ('.implode(', ', $delPageIds).') AND `area` = :area AND `order` > :order';
                    $command = Yii::app()->db->createCommand($sql);
                    $command->bindValue(':area', $pageWidget->area, PDO::PARAM_STR);
                    $command->bindValue(':order', $pageWidget->order, PDO::PARAM_INT);
                    $command->execute();
                }

                // Добавляем необходимые pageWidget`ы
                $addPageIds = array_diff($pageIds, $curPageIds);
                if (!empty($addPageIds)) {
                    $sql = 'UPDATE `' . PageWidget::tableName() . '` SET `order`=`order`+1 WHERE `page_id` IN ('.implode(', ', $addPageIds).') AND `area` = :area AND `order` >= :order';
                    $command = Yii::app()->db->createCommand($sql);
                    $command->bindValue(':area', $pageWidget->area, PDO::PARAM_STR);
                    $command->bindValue(':order', $pageWidget->order, PDO::PARAM_INT);
                    $command->execute();

                    $sql = 'INSERT INTO `' . PageWidget::tableName() . '` (`page_id`, `widget_id`, `order`, `area`) VALUES ';
                    $sqlArr = array();
                    foreach ($addPageIds as $id)
                    {
                        $sqlArr[] = '('.intval($id).', '.intval($this->id).', '.intval($pageWidget->order).', :area)';
                    }
                    $sql .= implode(',', $sqlArr);
                    $command = Yii::app()->db->createCommand($sql);
                    $command->bindValue(':area', $pageWidget->area, PDO::PARAM_STR);
                    $command->execute();
                }
            }
            $transaction->commit();
			return true;
		}
		catch(Exception $e) // в случае ошибки при выполнении запроса выбрасывается исключение
		{
			$transaction->rollBack();
			return false;
		}
    }

    /**
     * Устанавливает блок вверху или внизу области блоков на указанных страницах
     * @param array массив идентификаторов страниц, где размещается блок
     * @param boolean если true - разместить вверху, иначе - внизу области
     * @param string название области блоков
     */
    protected function setOnPagesTopOrBottom($pageIds, $onTop, $area)
    {
        if (!empty($pageIds) && is_array($pageIds)) {
            // Если разместить блок вверху
            if ($onTop) {
                // Оставить блок вверху, а то, что нужно подвинуть вниз
                $sql = 'UPDATE `' . PageWidget::tableName() . '` as pu
                        INNER JOIN (SELECT `order`, `page_id` FROM `' . PageWidget::tableName() . '`
                                    WHERE
                                        `page_id` IN ('.implode(', ', $pageIds) .')
                                        AND `area` = :area
                                        AND `order` = 0
                                        AND `widget_id` != :widget_id
                                    GROUP BY `page_id` ) as pu2
                                    ON pu.`page_id` = pu2.`page_id`
                        SET pu.`order` = pu.`order`+1
                        WHERE
                            pu.`area` = :area
                            AND pu2.`order` = 0
                            AND pu.`widget_id` != :widget_id';
                $command = Yii::app()->db->createCommand($sql);
                $command->bindValue(':area', $area, PDO::PARAM_STR);
                $command->bindValue(':widget_id', $this->id, PDO::PARAM_INT);
                $command->execute();
            } else {
                // Иначе, опустить блок вниз
                $sql = 'UPDATE `' . PageWidget::tableName() . '` as pu
                        INNER JOIN (SELECT MAX(`order`) as `m`, `page_id` FROM `' . PageWidget::tableName() . '`
                                    WHERE
                                        `page_id` IN ('.implode(', ', $pageIds) .')
                                    AND `area` = :area
                                    AND `widget_id` != :widget_id
                                    GROUP BY `page_id` ) as pu2
                        ON pu.`page_id` = pu2.`page_id`
                        SET pu.`order` = pu2.`m`+1
                        WHERE
                            pu.`widget_id` = :widget_id';
                $command = Yii::app()->db->createCommand($sql);
                $command->bindValue(':area', $area, PDO::PARAM_STR);
                $command->bindValue(':widget_id', $this->id, PDO::PARAM_INT);
                $command->execute();
            }
        }
    }

    /**
     * Обрабатывает перемещение блока
     * @param string название области 
     * @param array массив идентификаторов pageWidget'ов размещенных на странице, где делается перемещение
     * @param integer идентификатор перемещаемого pageWidget'а
     * @return boolean true в случае удачной операции, false - в обратном случае
     */
    public function move($area, $pageWidgetIds, $pageWidgetId)
    {
        $transaction=Yii::app()->db->beginTransaction();
		try
		{
            $pageWidget = PageWidget::model()->findByPk($pageWidgetId);
            $isNewArea = $pageWidget->area != $area;

            // Переносим блок в нужное место и сбрасываем сортировку
            $sql = 'UPDATE `' . PageWidget::tableName() . '` SET `area` = :area, `order` = 0
                    WHERE `widget_id` = :widget_id';
            $command = Yii::app()->db->createCommand($sql);
            $command->bindValue(':area', $area, PDO::PARAM_STR);
            $command->bindValue(':widget_id', $this->id, PDO::PARAM_INT);
            $command->execute();

            // Двигаем блоки на освободившееся место
            $sql = 'UPDATE `' . PageWidget::tableName() . '` as pu
                    INNER JOIN ( SELECT `page_id` FROM `' . PageWidget::tableName() . '`
                                WHERE `widget_id` = :widget_id ) as pu2
                    ON pu.`page_id` = pu2.`page_id`
                    SET pu.`order`= pu.`order`-1
                    WHERE
                        pu.`area` = :area
                        AND pu.`order` > :order';
            $command = Yii::app()->db->createCommand($sql);
            $command->bindValue(':widget_id', $this->id, PDO::PARAM_INT);
            $command->bindValue(':area', $pageWidget->area, PDO::PARAM_STR);
            $command->bindValue(':order', $pageWidget->order, PDO::PARAM_INT);
            $command->execute();

            // Выделяем списки блоков, которые идут перед и после перемещаемого блока
            $pageWidgetOrder = -1;
            foreach ($pageWidgetIds as $i=>$id) {
                $pageWidgetIds[$i] = intval($id);
                if ($pageWidgetId == $id) { $pageWidgetOrder = $i; }
            }
            $ids = array_flip($pageWidgetIds);
            $sql = 'SELECT `widget_id`, `id` FROM `' . PageWidget::tableName() . '`
                    WHERE `id` IN (' . implode(', ', $pageWidgetIds) . ')';
            $result = Yii::app()->db->createCommand($sql)->queryAll();
            $widgetIds = array();
            foreach ($result as $row) {
                $widgetIds[intval($ids[$row['id']])] = $row['widget_id'];
            }
            ksort($widgetIds);

            $preIds = array();
            $postIds = array();
            foreach ($widgetIds as $i=>$id) {
                if ($i < $pageWidgetOrder) {
                    $preIds[] = $id;
                } elseif ($i > $pageWidgetOrder) {
                    $postIds[] = $id;
                }
            }
            $preIds = array_reverse($preIds);
            $co = max(count($preIds), count($postIds));
            $_ids = array();
            for ($i = 0; $i < $co; $i++)
            {
                $_ids[] = array('id' => isset($preIds[$i]) ? $preIds[$i] : 0,
                                'pre' => true);
                $_ids[] = array('id' => isset($postIds[$i]) ? $postIds[$i] : 0,
                                'pre' => false);
            }

            // Находим страницы, где нужно правильно разместить перемещаемый блок
            $sql = 'SELECT * FROM `' . PageWidget::tableName() . '`
                    WHERE
                        `page_id` IN  ( SELECT `page_id` FROM `' . PageWidget::tableName() . '`
                                        WHERE `widget_id` = :widget_id )
                         AND `area` = :area
                         AND `widget_id` != :widget_id
                    ORDER BY `order`';
            $command = Yii::app()->db->createCommand($sql);
            $command->bindValue(':area', $area, PDO::PARAM_STR);
            $command->bindValue(':widget_id', $this->id, PDO::PARAM_INT);
            $result = $command->queryAll();
            $pages = array();
            $pageWidgets = array();
            foreach ($result as $row) {
                $pages[$row['page_id']][] = $row['widget_id'];
                $pageWidgets[$row['widget_id']][] = $row['id'];
                $widgets[$row['widget_id']][] = $row['page_id'];
            }

            // Отделяем страницы, где размещение пройдет просто, а где надо подумать
            $simplePages = $pages;
            foreach ($simplePages as $id=>$page) {
                if (count(array_intersect($page, $widgetIds))==0) {
                    unset($pages[$id]);
                } else {
                    unset($simplePages[$id]);
                }
            }

            // Страницы, где в нужной области нету блоков вообще,
            // дополнительно обрабатывать нету нужды.

            // Обработка страниц, у которых нету тех блоков, которые есть на текущей
            if (!empty($simplePages) && is_array($simplePages)) {
                $onTop = count($preIds) < count($postIds);
                $this->setOnPagesTopOrBottom(array_keys($simplePages), $onTop, $area);
            }

            // Обработка страниц, которые кроме своих блоков имеют также те блоки, которые
            // присутствуют на текущей странице. Самый сложный вариант.
            if (!empty($pages) && is_array($pages)) {
                // Обходим массив с идентификаторами юнитов, которые размещены
                // вокруг перемещаемого блока
                $pageIds = array_keys($pages);
                foreach ($_ids as $k=>$r) {
                    $id = $r['id'];
                    if (empty($pageIds)) {
                        break;
                    }

                    if (isset($widgets[$id]) && !empty($widgets[$id]) && is_array($widgets[$id])) {
                        $widgets[$id] = array_intersect($pageIds, $widgets[$id]);
                    }

                    // Если юнит размещен на какой-то странице
                    if (isset($widgets[$id]) && !empty($widgets[$id]) && is_array($widgets[$id])) {
                        // Подвинем соседей
                        $sql = 'UPDATE `' . PageWidget::tableName() . '` as pu
                                INNER JOIN (SELECT `order`, `page_id` FROM `' . PageWidget::tableName() . '`
                                            WHERE
                                                `page_id` IN ('.implode(', ', $widgets[$id]).')
                                            AND `widget_id` = :sibling_widget_id
                                            GROUP BY `page_id` ) as pu2
                                ON pu.`page_id` = pu2.`page_id`
                                SET pu.`order` = pu.`order`+1
                                WHERE
                                    pu.`area` = :area
                                    AND pu.`widget_id` != :widget_id
                                    AND pu.`order` '.($r['pre'] ? '>' : '>=') .' pu2.`order`';
                        $command = Yii::app()->db->createCommand($sql);
                        $command->bindValue(':area', $area, PDO::PARAM_STR);
                        $command->bindValue(':widget_id', $this->id, PDO::PARAM_INT);
                        $command->bindValue(':sibling_widget_id', $id, PDO::PARAM_INT);
                        $command->execute();

                        // Установка перемещаемого блока в нужное место
                        $sql = 'UPDATE `' . PageWidget::tableName() . '` as pu
                                INNER JOIN (SELECT `order`, `page_id` FROM `' . PageWidget::tableName() . '`
                                            WHERE
                                                `page_id` IN ('.implode(', ', $widgets[$id]).')
                                            AND `widget_id` = :sibling_widget_id
                                            GROUP BY `page_id` ) as pu2
                                ON pu.`page_id` = pu2.`page_id`
                                SET pu.`order` = pu2.`order`'.($r['pre'] ? '+1' : '-1') .'
                                WHERE
                                    pu.`widget_id` = :widget_id';
                        $command = Yii::app()->db->createCommand($sql);
                        $command->bindValue(':widget_id', $this->id, PDO::PARAM_INT);
                        $command->bindValue(':sibling_widget_id', $id, PDO::PARAM_INT);
                        $command->execute();

                        // Из массива страниц убираем уже обработанные
                        $pageIds = array_diff($pageIds, $widgets[$id]);
                    } elseif (($id == 0)&&($k < 2)) {
                        $this->setOnPagesTopOrBottom($pageIds, $r['pre'], $area);
                        $pageIds = array();
                    }
                }
            }
            $transaction->commit();
			return true;
		}
		catch(Exception $e) // в случае ошибки при выполнении запроса выбрасывается исключение
		{
			$transaction->rollBack();
			return false;
		}

    }
/*    
    public static function getWidgetTypeByClassName($className)
    {
        return strtolower(str_replace('Widget', '', $className));
    }

    public static function getClassNameByWidgetType($widgetType)
    {
        $widgetType = strtolower($widgetType);
        if (substr($widgetType,0,4) != 'widget')
            return 'Widget'.ucfirst($widgetType);
        else
            return 'Widget'.ucfirst(substr($widgetType,4));
    }

*/
}
