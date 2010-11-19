<?php

class Page extends CActiveRecord
{
	protected $_path;
	
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'pages';
	}

	public function rules()
	{
		return array(
			array('parent_id, title', 'required'),
			array('parent_id, order, active', 'numerical', 'integerOnly'=>true),
			array('path, title, keywords, description, redirect', 'length', 'max'=>255),
		);
	}
	
	public function relations()
	{
		return array(
			'parent'=>array(self::BELONGS_TO, 'Page', 'parent_id'),
			'children'=>array(self::HAS_MANY, 'Page', 'parent_id', 
                'order'=>'`order`'
            ),
			'childrenCount'=>array(self::STAT, 'Page', 'parent_id'),
		);
	}

	public function scopes()
	{
		return array(
            'order' => array(
                'order'=>'`order`',
            ),
		);
	}

    public function childrenPages()
    {
        $this->getDbCriteria()->mergeWith(array(
            'condition'=>'parent_id = :id',
            'params'=>array(
                ':id' => $this->id
            ),
        ));
        return $this;
    }

	public function getUnits($area='', $exclude=false)
	{
		$condition = '`t`.`page_id` = :id';
		$params = array(':id'=>$this->id);
		if (!is_array($area)) {
			$area = array($area);
		}
		if ($area) {
			foreach ($area as $i=>$ar) {
				$sign = $exclude ? '<>' : '=';
				$condition .= " AND (`t`.`area` {$sign} :area{$i})";
				$params[':area'.$i] = $ar;
			}
		}
		
		return PageUnit::model()->findAll(array(
			'condition' => $condition,
			'with' => array('unit'),
			'order' => '`t`.`area`, `t`.`order`',
			'params' => $params
		));
	}

	public static function getTree($exclude=array(),$exclude_children=true)
	{
		$criteria['order'] = '`order`';
		if (!empty($exclude))
		{
			if (!is_array($exclude)) $exclude = array($exclude);
			$criteria['condition'] = '`id` NOT IN ('.implode(',',$exclude).')';
		}
		$pages = Page::model()->findAll($criteria);
		$tree = array();
		foreach ($pages as $page) {
			if ($exclude_children && !empty($exclude))
			{
				$ids = explode(',',$page->path);
				$intersect = array_intersect($exclude, $ids);
				if (!empty($intersect)) continue;
			}
			$tree[$page->path][] = $page;
		}
		return $tree;
	}
	
    public function selectPage($number, $per_page=0)
    {
        if ($per_page<1)
            $per_page = Yii::app()->settings->getValue('defaultsPerPage');

        $offset = ($number-1)*$per_page;
        if ($offset < 0)
            $offset = 0;
        $this->getDbCriteria()->mergeWith(array(
            'limit'=>$per_page,
            'offset'=>$offset
        ));
        return $this;
    }

    public function beforeSave()
	{
		$this->_path = $this->path;
		$newpath = $this->generatePath(true);
		if ($this->_path != $newpath) {
			$this->path = $newpath;
		}
		return true;
	}
	
	public function afterSave()
	{
		if ($this->_path != $this->path)
		{
			$children = $this->children;
			foreach ($children as $page) {
				$page->path = $page->generatePath(true);
				$page->save(false);
			}
		}
		return true;
	}
	
	public function afterDelete()
	{
		PageUnit::model()->deleteAll('page_id = :page_id', array(':page_id' => $this->id));
		
		// Удаляем все блоки, которые больше нигде не размещены
		$sql = 'SELECT `unit`.`id`, `unit`.`type`  FROM `' . Unit::tableName() . '` as `unit`
				LEFT JOIN `' . PageUnit::tableName() . '` as `pageunit` ON (`unit`.`id` = `pageunit`.`unit_id`)
				WHERE `pageunit`.`id` IS NULL';
		$pus = Yii::app()->db->createCommand($sql)->queryAll();
		$ids = array();
		if ($pus && is_array($pus))
		{
			foreach ($pus as $pu)
			{
				$ids[] = intval($pu['id']);
                $tmp_class = Unit::getClassNameByUnitType($pu['type']);
				$tmp_class::model()->deleteAll('unit_id = ' . intval($pu['id']));
			}
			$sql = 'DELETE FROM `' . Unit::tableName() . '` WHERE `id` IN (' . implode(',',$ids) . ')';
			Yii::app()->db->createCommand($sql)->execute();
		}
	}
	
	public function deleteWithChildren()
	{
		$children = $this->children;
		if ($children)
			foreach ($children as $page)
			{
				$page->deleteWithChildren();
			}
		$this->delete();
	}

	public function generatePath($full=false)
	{
		$ret = array();
		if ($this->parent_id == 0) {
			$ret[] = $this->parent_id;
		} else {
			if ($full) {
				$ret[] = $this->parent->generatePath($full);
			} else
				$ret[] = $this->parent->path;
			$ret[] = $this->parent_id;
		}
		return implode(',',$ret);
	}

	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'parent_id' => 'Родительская страница',
			'title' => 'Заголовок',
			'keywords' => 'Ключевые слова',
			'description' => 'Описание',
			'order' => 'Order',
			'active' => 'Включено',
            'redirect' => 'Переадресация',
		);
	}
	
	public static function form()
	{
		return array(
//			'title'=>'Свойства страницы',
			'elements'=>array(
                Form::tab('Свойства страницы'),
				'title'=>array(
					'type'=>'text',
					'maxlength'=>255,
					'size'=>60
				),
/*				'active'=>array(
					'type'=>'checkbox'
				),*/
				'parent_id'=>array(
					'type'=>'PageSelect',
				),
				'keywords'=>array(
					'type'=>'textarea',
					'rows'=>4,
					'cols'=>40
				),
				'description'=>array(
					'type'=>'textarea',
					'rows'=>4,
					'cols'=>40
				),
                Form::tab('Дополнительно'),
                'redirect'=>array(
                    'type'=>'Link',
                    'showFileManagerButton'=>false,
                    'showUploadButton'=>false
                )
			),
			'buttons'=>array(
				'save'=>array(
					'type'=>'submit',
					'label'=>'Сохранить',
					'title'=>'Сохранить и закрыть окно'
				),
			)
		);
	}

    public function isSimilarTo($page, $areas=array(), $unit_id=0)
    {
        $id = is_object($page) ? $page->id : $page;
        $sql = 'SELECT `unit_id` FROM `' . PageUnit::tableName() . '`
                WHERE `page_id` = :id
                      AND `area` ' . (!empty($areas) ? (is_array($areas) ? 'IN ('.implode(',',$areas).')' : ' = `area`') : 'NOT LIKE "main%"').'
                      '.($unit_id ? ' AND `unit_id` != '.intval($unit_id) : '');
        $command = Yii::app()->db->createCommand($sql);
        $command->bindParam(':id', $this->id, PDO::PARAM_INT);
        $arr = $command->queryColumn();

        $command = Yii::app()->db->createCommand($sql);
        $command->bindParam(':id', $id, PDO::PARAM_INT);
        $ret = array_diff($arr, $command->queryColumn());
        return empty($ret);
    }
	
	// Проверяем каждую область и вставляем блоки с родительской страницы со всех областей, кроме main
	public function fill()
	{
        $sql = 'SELECT * FROM `' . PageUnit::tableName() . '` WHERE `page_id` = :page_id AND `area` NOT LIKE "main%"';
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(':page_id', $this->parent_id, PDO::PARAM_INT);
        $pus = $command->queryAll();
        if ($pus && is_array($pus))
        {
            $sql = 'INSERT INTO `' . PageUnit::tableName() . '` (`page_id`, `unit_id`, `order`, `area`) VALUES ';
            $sql_arr = array();
            foreach ($pus as $pu)
            {
                $sql_arr[] = '('.intval($this->id).', '.intval($pu['unit_id']).', '.intval($pu['order']).', "'.$pu['area'].'")';
            }
            $sql .= implode(',', $sql_arr);
            $command = Yii::app()->db->createCommand($sql);
            $command->execute();
        }
	}
	
	public static function defaultObject()
	{
		$obj = new self;
		$obj->title = 'Новая страница созданная ' . date("Y-m-d H:i:s");
		$obj->active = true;
		$obj->order = 0;
		return $obj;
	}

}