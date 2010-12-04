<?php

class Unit extends I18nActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'units';
	}

	public function rules()
	{
		return $this->localizedRules(array(
			array('type', 'required'),
			array('type', 'length', 'max'=>64),
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
			'pages' => array(self::MANY_MANY, 'Page', 'pages_units(unit_id,page_id)')
		);
	}

	public function attributeLabels()
	{
		return array(
//			'id' => 'ID',
//			'type' => Yii::t('cms', 'Type),
			'title' => Yii::t('cms', 'Title'),
            'template' => Yii::t('cms', 'Template'),
		);
	}

	/**
     * Возвращает объект содержащий контент блока
     * @return mixed объект содержащий контент блока
     */
    public function getContent()
	{
        $tmp_class = Unit::getClassNameByUnitType($this->type);
		return $tmp_class::model()->find('unit_id=:id', array(':id'=>$this->id));
	}

    public function getUnitUrl()
    {
        $sql = 'SELECT page_id FROM `' . PageUnit::tableName() . '` WHERE unit_id = :unit_id ORDER BY id LIMIT 1';
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(':unit_id', $this->id, PDO::PARAM_INT);
        $page_id = $command->queryScalar();
        return Yii::app()->controller->createUrl('page/view', array('id'=>$page_id));
    }

    /**
     * Возвращает список типов блоков, установленных в CMS
     * @return array список типов блоков, установленных в CMS
     */
    public static function getTypes()
	{
        self::loadTypes();
        $config = Yii::getPathOfAlias('application.units.config').'.php';
        $classNames = array_keys(include($config));
		foreach ($classNames as $className) {
            if (!Yii::app()->settings->getValue('simpleMode') || !$className::HIDDEN)
                $ret[$className] = $className::name();
            
		}
        asort($ret);
		return array_keys($ret);
	}

    public static function loadTypes()
    {
        $config = Yii::getPathOfAlias('application.units.config').'.php';
        $map = include($config);
        foreach ($map as $className => $alias) {
            Yii::$classMap[$className] = Yii::getPathOfAlias($alias.'.'.$className.'.unit').'.php';
        }

    }
	
	public function beforeDelete()
	{
		return $this->content->delete();
	}

    /**
     * Устанавливает юнит на конкретной странице в определенном месте
     * 
     * @param integer id страницы
     * @param string название области блоков
     * @param integer номер по порядку размещения блоков
     * @return PageUnit
     */
    public function setOnPage($page_id, $area, $order)
    {
        // Раздвигаем последующие юниты
        $sql = 'UPDATE `' . PageUnit::tableName() . '` SET `order`=`order`+1 WHERE `page_id` = :page_id AND `area` = :area AND `order` > :order';
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(':page_id', intval($page_id), PDO::PARAM_INT);
        $command->bindValue(':area', $area, PDO::PARAM_STR);
        $command->bindValue(':order', $order, PDO::PARAM_INT);
        $command->execute();

        // Устанавливаем юнит
        $pageunit = new PageUnit;
        $pageunit->page_id = intval($page_id);
        $pageunit->unit_id = $this->id;
        $pageunit->order = $order+1;
        $pageunit->area = $area;
        $pageunit->save();

        return $pageunit;
    }

    /**
     * Устанавливает юнит только на конкретных страницах
     * @param array id страниц, где должен быть размещен юнит
     * @param integer id pageunit'а, на основе которого делается размещение на других страницах
     * @return boolean true в случае удачной операции, false - в обратном случае
     */
    public function setOnPagesOnly($page_ids, $pageunit_id)
    {
        $transaction=Yii::app()->db->beginTransaction();
		try
		{
            $pageunit = PageUnit::model()->findByPk($pageunit_id);
            if ($pageunit) {
                if (empty($page_ids)) {
                    $page_ids = array($pageunit->page_id);
                }

                $sql = 'SELECT `page_id` FROM `' . PageUnit::tableName() . '` WHERE unit_id = :unit_id';
                $command = Yii::app()->db->createCommand($sql);
                $command->bindValue(':unit_id', $this->id, PDO::PARAM_INT);
                $cur_page_ids = $command->queryColumn();

                // Удаляем лишние pageunit`ы
                $del_page_ids = array_diff($cur_page_ids, $page_ids);
                if (!empty($del_page_ids)) {
                    $sql = 'DELETE FROM `' . PageUnit::tableName() . '` WHERE unit_id = :unit_id AND `page_id` IN (' . implode(', ',$del_page_ids) . ')';
                    $command = Yii::app()->db->createCommand($sql);
                    $command->bindValue(':unit_id', $this->id, PDO::PARAM_INT);
                    $command->execute();

                    $sql = 'UPDATE `' . PageUnit::tableName() . '` SET `order`=`order`-1 WHERE `page_id` IN ('.implode(', ', $del_page_ids).') AND `area` = :area AND `order` > :order';
                    $command = Yii::app()->db->createCommand($sql);
                    $command->bindValue(':area', $pageunit->area, PDO::PARAM_STR);
                    $command->bindValue(':order', $pageunit->order, PDO::PARAM_INT);
                    $command->execute();
                }

                // Добавляем необходимые pageunit`ы
                $add_page_ids = array_diff($page_ids, $cur_page_ids);
                if (!empty($add_page_ids)) {
                    $sql = 'UPDATE `' . PageUnit::tableName() . '` SET `order`=`order`+1 WHERE `page_id` IN ('.implode(', ', $add_page_ids).') AND `area` = :area AND `order` >= :order';
                    $command = Yii::app()->db->createCommand($sql);
                    $command->bindValue(':area', $pageunit->area, PDO::PARAM_STR);
                    $command->bindValue(':order', $pageunit->order, PDO::PARAM_INT);
                    $command->execute();

                    $sql = 'INSERT INTO `' . PageUnit::tableName() . '` (`page_id`, `unit_id`, `order`, `area`) VALUES ';
                    $sql_arr = array();
                    foreach ($add_page_ids as $id)
                    {
                        $sql_arr[] = '('.intval($id).', '.intval($this->id).', '.intval($pageunit->order).', :area)';
                    }
                    $sql .= implode(',', $sql_arr);
                    $command = Yii::app()->db->createCommand($sql);
                    $command->bindValue(':area', $pageunit->area, PDO::PARAM_STR);
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
    protected function setOnPagesTopOrBottom($page_ids, $on_top, $area)
    {
        if (!empty($page_ids) && is_array($page_ids)) {
            // Если разместить блок вверху
            if ($on_top) {
                // Оставить блок вверху, а то, что нужно подвинуть вниз
                $sql = 'UPDATE `' . PageUnit::tableName() . '` as pu
                        INNER JOIN (SELECT `order`, `page_id` FROM `' . PageUnit::tableName() . '`
                                    WHERE
                                        `page_id` IN ('.implode(', ', $page_ids) .')
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
                                        `page_id` IN ('.implode(', ', $page_ids) .')
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
     * @param array массив идентификаторов pageunit'ов размещенных на странице, где делается перемещение
     * @param integer идентификатор перемещаемого pageunit'а
     * @return boolean true в случае удачной операции, false - в обратном случае
     */
    public function move($area, $pageunit_ids, $pageunit_id)
    {
        $transaction=Yii::app()->db->beginTransaction();
		try
		{
            $pageunit = PageUnit::model()->findByPk($pageunit_id);
            $is_new_area = $pageunit->area != $area;

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
            $command->bindValue(':area', $pageunit->area, PDO::PARAM_STR);
            $command->bindValue(':order', $pageunit->order, PDO::PARAM_INT);
            $command->execute();

            // Выделяем списки блоков, которые идут перед и после перемещаемого блока
            $pageunit_order = -1;
            foreach ($pageunit_ids as $i=>$id) {
                $pageunit_ids[$i] = intval($id);
                if ($pageunit_id == $id) { $pageunit_order = $i; }
            }
            $ids = array_flip($pageunit_ids);
            $sql = 'SELECT `unit_id`, `id` FROM `' . PageUnit::tableName() . '`
                    WHERE `id` IN (' . implode(', ', $pageunit_ids) . ')';
            $result = Yii::app()->db->createCommand($sql)->queryAll();
            $unit_ids = array();
            foreach ($result as $row) {
                $unit_ids[intval($ids[$row['id']])] = $row['unit_id'];
            }
            ksort($unit_ids);

            $pre_ids = array();
            $post_ids = array();
            foreach ($unit_ids as $i=>$id) {
                if ($i < $pageunit_order) {
                    $pre_ids[] = $id;
                } elseif ($i > $pageunit_order) {
                    $post_ids[] = $id;
                }
            }
            $pre_ids = array_reverse($pre_ids);
            $co = max(count($pre_ids), count($post_ids));
            $_ids = array();
            for ($i = 0; $i < $co; $i++)
            {
                $_ids[] = array('id' => isset($pre_ids[$i]) ? $pre_ids[$i] : 0,
                                'pre' => true);
                $_ids[] = array('id' => isset($post_ids[$i]) ? $post_ids[$i] : 0,
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
            $pageunits = array();
            foreach ($result as $row) {
                $pages[$row['page_id']][] = $row['unit_id'];
                $pageunits[$row['unit_id']][] = $row['id'];
                $units[$row['unit_id']][] = $row['page_id'];
            }

            // Отделяем страницы, где размещение пройдет просто, а где надо подумать
            $simple_pages = $pages;
            foreach ($simple_pages as $id=>$page) {
                if (count(array_intersect($page, $unit_ids))==0) {
                    unset($pages[$id]);
                } else {
                    unset($simple_pages[$id]);
                }
            }

            // Страницы, где в нужной области нету блоков вообще,
            // дополнительно обрабатывать нету нужды.

            // Обработка страниц, у которых нету тех блоков, которые есть на текущей
            if (!empty($simple_pages) && is_array($simple_pages)) {
                $on_top = count($pre_ids) < count($post_ids);
                $this->setOnPagesTopOrBottom(array_keys($simple_pages), $on_top, $area);
            }

            // Обработка страниц, которые кроме своих блоков имеют также те блоки, которые
            // присутствуют на текущей странице. Самый сложный вариант.
            if (!empty($pages) && is_array($pages)) {
                // Обходим массив с идентификаторами юнитов, которые размещены
                // вокруг перемещаемого блока
                $page_ids = array_keys($pages);
                foreach ($_ids as $k=>$r) {
                    $id = $r['id'];
                    if (empty($page_ids)) {
                        break;
                    }

                    if (isset($units[$id]) && !empty($units[$id]) && is_array($units[$id])) {
                        $units[$id] = array_intersect($page_ids, $units[$id]);
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
                        $page_ids = array_diff($page_ids, $units[$id]);
                    } elseif (($id == 0)&&($k < 2)) {
                        $this->setOnPagesTopOrBottom($page_ids, $r['pre'], $area);
                        $page_ids = array();
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


}
