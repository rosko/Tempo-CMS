<?php

class Unit extends I18nActiveRecord
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
            'template' => 'char(32)',
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
			array('template', 'length', 'max'=>32),
		));
	}

    public function i18n()
    {
        return array('title');
    }

	public function relations()
	{
		return array(
			'pages' => array(self::MANY_MANY, 'Page', Yii::app()->db->tablePrefix.'pages_units(unit_id,page_id)')
		);
	}

    public function  behaviors() {
        return array(
            'CTimestampBehavior' => array(
                'class' => 'zii.behaviors.CTimestampBehavior',
                'createAttribute' => 'create',
                'updateAttribute' => 'modify',
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
                'label'=>'Create unit', // Право создавать блоки
                'defaultRoles'=>array('author', 'editor', 'administrator'),
            ),
            'read'=>array(
                'label'=>'View unit', // Право просматривать блоки
                'defaultRoles'=>array('anybody'),
            ),
            'update'=>array(
                'label'=>'Update unit', // Право редактировать блоки
                'defaultRoles'=>array('editor' , 'administrator'),
            ),
            'updateAccess'=>array(
                'label'=>'Update unit access', // Право редактировать права доступа к блокам
                'defaultRoles'=>array('administrator'),
            ),
            'move'=>array(
                'label'=>'Move unit', // Право перемещать блоки
                'defaultRoles'=>array('administrator', 'editor'),
            ),
            'delete'=>array(
                'label'=>'Delete unit', // Право удалять блоки
                'defaultRoles'=>array('editor', 'administrator'),
            ),
            'manage'=>array(
                'label'=>'Move unit', // Право инсталлировать/деинсталлировать юниты
                'defaultRoles'=>array('administrator'),
            ),
        );
    }

    public function tasks()
    {
        return array(
            'readOwn'=>array(
                'label'=>'View own unit',
                'bizRule'=>'return Yii::app()->user->id==$params["unit"]->author_id;',
                'children'=>array('readUnit'),
                'defaultRoles'=>array('author', 'authenticated'),
            ),
            'updateOwn'=>array(
                'label'=>'Edit own unit',
                'bizRule'=>'return Yii::app()->user->id==$params["unit"]->author_id;',
                'children'=>array('updateUnit'),
                'defaultRoles'=>array('author', 'authenticated'),
            ),
            'updateAccessOwn'=>array(
                'label'=>'Edit own unit access',
                'bizRule'=>'return Yii::app()->user->id==$params["unit"]->author_id;',
                'children'=>array('updateAccessUnit'),
                'defaultRoles'=>array('author'),
            ),
            'deleteOwn'=>array(
                'label'=>'Delete own unit',
                'bizRule'=>'return Yii::app()->user->id==$params["unit"]->author_id;',
                'children'=>array('deleteUnit'),
                'defaultRoles'=>array('author'),
            ),
        );
    }

    public function unitsDirsAliases()
    {
        return array(
            'application.units',
            'local.units'
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
		return call_user_func(array($modelClass, 'model'))->find('unit_id=:id', array(':id'=>$this->id));
	}

    /**
     * Находит одну страницу где размещен экземпляр юнита
     *
     * @return array свойства страницы
     */
    public function getUnitPageArray()
    {
        $sql = 'SELECT p.* FROM `'.Page::tableName().'`  as p INNER JOIN `' . PageUnit::tableName() . '` as pu ON pu.page_id = p.id WHERE pu.unit_id = :unit_id ORDER BY pu.id LIMIT 1';
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(':unit_id', $this->id, PDO::PARAM_INT);
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
    public function getUnitUrl($absolute=false, $params=array())
    {
        $page = $this->getUnitPageArray();
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
     * @return PageUnit
     */
    public function setOnPage($pageId, $area, $order)
    {
        $pageId = (int)$pageId;
        // Раздвигаем последующие юниты
        $sql = 'UPDATE `' . PageUnit::tableName() . '` SET `order`=`order`+1 WHERE `page_id` = :page_id AND `area` = :area AND `order` > :order';
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(':page_id', $pageId, PDO::PARAM_INT);
        $command->bindValue(':area', $area, PDO::PARAM_STR);
        $command->bindValue(':order', $order, PDO::PARAM_INT);
        $command->execute();

        // Устанавливаем юнит
        $pageUnit = new PageUnit;
        $pageUnit->page_id = $pageId;
        $pageUnit->unit_id = $this->id;
        $pageUnit->order = $order+1;
        $pageUnit->area = $area;
        $pageUnit->save();

        return $pageUnit;
    }

    /**
     * Устанавливает юнит только на конкретных страницах
     * @param array id страниц, где должен быть размещен юнит
     * @param integer id pageUnit'а, на основе которого делается размещение на других страницах
     * @return boolean true в случае удачной операции, false - в обратном случае
     */
    public function setOnPagesOnly($pageIds, $pageUnitId)
    {
        $transaction=Yii::app()->db->beginTransaction();
		try
		{
            $pageUnit = PageUnit::model()->findByPk($pageUnitId);
            if ($pageUnit) {
                if (empty($pageIds)) {
                    $pageIds = array($pageUnit->page_id);
                }

                $sql = 'SELECT `page_id` FROM `' . PageUnit::tableName() . '` WHERE unit_id = :unit_id';
                $command = Yii::app()->db->createCommand($sql);
                $command->bindValue(':unit_id', $this->id, PDO::PARAM_INT);
                $curPageIds = $command->queryColumn();

                // Удаляем лишние pageUnit`ы
                $delPageIds = array_diff($curPageIds, $pageIds);
                if (!empty($delPageIds)) {
                    $sql = 'DELETE FROM `' . PageUnit::tableName() . '` WHERE unit_id = :unit_id AND `page_id` IN (' . implode(', ',$delPageIds) . ')';
                    $command = Yii::app()->db->createCommand($sql);
                    $command->bindValue(':unit_id', $this->id, PDO::PARAM_INT);
                    $command->execute();

                    $sql = 'UPDATE `' . PageUnit::tableName() . '` SET `order`=`order`-1 WHERE `page_id` IN ('.implode(', ', $delPageIds).') AND `area` = :area AND `order` > :order';
                    $command = Yii::app()->db->createCommand($sql);
                    $command->bindValue(':area', $pageUnit->area, PDO::PARAM_STR);
                    $command->bindValue(':order', $pageUnit->order, PDO::PARAM_INT);
                    $command->execute();
                }

                // Добавляем необходимые pageUnit`ы
                $addPageIds = array_diff($pageIds, $curPageIds);
                if (!empty($addPageIds)) {
                    $sql = 'UPDATE `' . PageUnit::tableName() . '` SET `order`=`order`+1 WHERE `page_id` IN ('.implode(', ', $addPageIds).') AND `area` = :area AND `order` >= :order';
                    $command = Yii::app()->db->createCommand($sql);
                    $command->bindValue(':area', $pageUnit->area, PDO::PARAM_STR);
                    $command->bindValue(':order', $pageUnit->order, PDO::PARAM_INT);
                    $command->execute();

                    $sql = 'INSERT INTO `' . PageUnit::tableName() . '` (`page_id`, `unit_id`, `order`, `area`) VALUES ';
                    $sqlArr = array();
                    foreach ($addPageIds as $id)
                    {
                        $sqlArr[] = '('.intval($id).', '.intval($this->id).', '.intval($pageUnit->order).', :area)';
                    }
                    $sql .= implode(',', $sqlArr);
                    $command = Yii::app()->db->createCommand($sql);
                    $command->bindValue(':area', $pageUnit->area, PDO::PARAM_STR);
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
                $sql = 'UPDATE `' . PageUnit::tableName() . '` as pu
                        INNER JOIN (SELECT `order`, `page_id` FROM `' . PageUnit::tableName() . '`
                                    WHERE
                                        `page_id` IN ('.implode(', ', $pageIds) .')
                                        AND `area` = :area
                                        AND `order` = 0
                                        AND `unit_id` != :unit_id
                                    GROUP BY `page_id` ) as pu2
                                    ON pu.`page_id` = pu2.`page_id`
                        SET pu.`order` = pu.`order`+1
                        WHERE
                            pu.`area` = :area
                            AND pu2.`order` = 0
                            AND pu.`unit_id` != :unit_id';
                $command = Yii::app()->db->createCommand($sql);
                $command->bindValue(':area', $area, PDO::PARAM_STR);
                $command->bindValue(':unit_id', $this->id, PDO::PARAM_INT);
                $command->execute();
            } else {
                // Иначе, опустить блок вниз
                $sql = 'UPDATE `' . PageUnit::tableName() . '` as pu
                        INNER JOIN (SELECT MAX(`order`) as `m`, `page_id` FROM `' . PageUnit::tableName() . '`
                                    WHERE
                                        `page_id` IN ('.implode(', ', $pageIds) .')
                                    AND `area` = :area
                                    AND `unit_id` != :unit_id
                                    GROUP BY `page_id` ) as pu2
                        ON pu.`page_id` = pu2.`page_id`
                        SET pu.`order` = pu2.`m`+1
                        WHERE
                            pu.`unit_id` = :unit_id';
                $command = Yii::app()->db->createCommand($sql);
                $command->bindValue(':area', $area, PDO::PARAM_STR);
                $command->bindValue(':unit_id', $this->id, PDO::PARAM_INT);
                $command->execute();
            }
        }
    }

    /**
     * Обрабатывает перемещение блока
     * @param string название области 
     * @param array массив идентификаторов pageUnit'ов размещенных на странице, где делается перемещение
     * @param integer идентификатор перемещаемого pageUnit'а
     * @return boolean true в случае удачной операции, false - в обратном случае
     */
    public function move($area, $pageUnitIds, $pageUnitId)
    {
        $transaction=Yii::app()->db->beginTransaction();
		try
		{
            $pageUnit = PageUnit::model()->findByPk($pageUnitId);
            $isNewArea = $pageUnit->area != $area;

            // Переносим блок в нужное место и сбрасываем сортировку
            $sql = 'UPDATE `' . PageUnit::tableName() . '` SET `area` = :area, `order` = 0
                    WHERE `unit_id` = :unit_id';
            $command = Yii::app()->db->createCommand($sql);
            $command->bindValue(':area', $area, PDO::PARAM_STR);
            $command->bindValue(':unit_id', $this->id, PDO::PARAM_INT);
            $command->execute();

            // Двигаем блоки на освободившееся место
            $sql = 'UPDATE `' . PageUnit::tableName() . '` as pu
                    INNER JOIN ( SELECT `page_id` FROM `' . PageUnit::tableName() . '`
                                WHERE `unit_id` = :unit_id ) as pu2
                    ON pu.`page_id` = pu2.`page_id`
                    SET pu.`order`= pu.`order`-1
                    WHERE
                        pu.`area` = :area
                        AND pu.`order` > :order';
            $command = Yii::app()->db->createCommand($sql);
            $command->bindValue(':unit_id', $this->id, PDO::PARAM_INT);
            $command->bindValue(':area', $pageUnit->area, PDO::PARAM_STR);
            $command->bindValue(':order', $pageUnit->order, PDO::PARAM_INT);
            $command->execute();

            // Выделяем списки блоков, которые идут перед и после перемещаемого блока
            $pageUnitOrder = -1;
            foreach ($pageUnitIds as $i=>$id) {
                $pageUnitIds[$i] = intval($id);
                if ($pageUnitId == $id) { $pageUnitOrder = $i; }
            }
            $ids = array_flip($pageUnitIds);
            $sql = 'SELECT `unit_id`, `id` FROM `' . PageUnit::tableName() . '`
                    WHERE `id` IN (' . implode(', ', $pageUnitIds) . ')';
            $result = Yii::app()->db->createCommand($sql)->queryAll();
            $unitIds = array();
            foreach ($result as $row) {
                $unitIds[intval($ids[$row['id']])] = $row['unit_id'];
            }
            ksort($unitIds);

            $preIds = array();
            $postIds = array();
            foreach ($unitIds as $i=>$id) {
                if ($i < $pageUnitOrder) {
                    $preIds[] = $id;
                } elseif ($i > $pageUnitOrder) {
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
            $sql = 'SELECT * FROM `' . PageUnit::tableName() . '`
                    WHERE
                        `page_id` IN  ( SELECT `page_id` FROM `' . PageUnit::tableName() . '`
                                        WHERE `unit_id` = :unit_id )
                         AND `area` = :area
                         AND `unit_id` != :unit_id
                    ORDER BY `order`';
            $command = Yii::app()->db->createCommand($sql);
            $command->bindValue(':area', $area, PDO::PARAM_STR);
            $command->bindValue(':unit_id', $this->id, PDO::PARAM_INT);
            $result = $command->queryAll();
            $pages = array();
            $pageUnits = array();
            foreach ($result as $row) {
                $pages[$row['page_id']][] = $row['unit_id'];
                $pageUnits[$row['unit_id']][] = $row['id'];
                $units[$row['unit_id']][] = $row['page_id'];
            }

            // Отделяем страницы, где размещение пройдет просто, а где надо подумать
            $simplePages = $pages;
            foreach ($simplePages as $id=>$page) {
                if (count(array_intersect($page, $unitIds))==0) {
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

                    if (isset($units[$id]) && !empty($units[$id]) && is_array($units[$id])) {
                        $units[$id] = array_intersect($pageIds, $units[$id]);
                    }

                    // Если юнит размещен на какой-то странице
                    if (isset($units[$id]) && !empty($units[$id]) && is_array($units[$id])) {
                        // Подвинем соседей
                        $sql = 'UPDATE `' . PageUnit::tableName() . '` as pu
                                INNER JOIN (SELECT `order`, `page_id` FROM `' . PageUnit::tableName() . '`
                                            WHERE
                                                `page_id` IN ('.implode(', ', $units[$id]).')
                                            AND `unit_id` = :sibling_unit_id
                                            GROUP BY `page_id` ) as pu2
                                ON pu.`page_id` = pu2.`page_id`
                                SET pu.`order` = pu.`order`+1
                                WHERE
                                    pu.`area` = :area
                                    AND pu.`unit_id` != :unit_id
                                    AND pu.`order` '.($r['pre'] ? '>' : '>=') .' pu2.`order`';
                        $command = Yii::app()->db->createCommand($sql);
                        $command->bindValue(':area', $area, PDO::PARAM_STR);
                        $command->bindValue(':unit_id', $this->id, PDO::PARAM_INT);
                        $command->bindValue(':sibling_unit_id', $id, PDO::PARAM_INT);
                        $command->execute();

                        // Установка перемещаемого блока в нужное место
                        $sql = 'UPDATE `' . PageUnit::tableName() . '` as pu
                                INNER JOIN (SELECT `order`, `page_id` FROM `' . PageUnit::tableName() . '`
                                            WHERE
                                                `page_id` IN ('.implode(', ', $units[$id]).')
                                            AND `unit_id` = :sibling_unit_id
                                            GROUP BY `page_id` ) as pu2
                                ON pu.`page_id` = pu2.`page_id`
                                SET pu.`order` = pu2.`order`'.($r['pre'] ? '+1' : '-1') .'
                                WHERE
                                    pu.`unit_id` = :unit_id';
                        $command = Yii::app()->db->createCommand($sql);
                        $command->bindValue(':unit_id', $this->id, PDO::PARAM_INT);
                        $command->bindValue(':sibling_unit_id', $id, PDO::PARAM_INT);
                        $command->execute();

                        // Из массива страниц убираем уже обработанные
                        $pageIds = array_diff($pageIds, $units[$id]);
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
    public static function getUnitTypeByClassName($className)
    {
        return strtolower(str_replace('Unit', '', $className));
    }

    public static function getClassNameByUnitType($unitType)
    {
        $unitType = strtolower($unitType);
        if (substr($unitType,0,4) != 'unit')
            return 'Unit'.ucfirst($unitType);
        else
            return 'Unit'.ucfirst(substr($unitType,4));
    }

*/
}
