<?php

class Page extends I18nActiveRecord
{
	protected $_path;
    protected $_url = array();
    protected $_units = null;
	
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->db->tablePrefix . 'pages';
	}

	public function rules()
	{
        $rules = array(
			array('parent_id, title', 'required'),
			array('parent_id, order, active', 'numerical', 'integerOnly'=>true),
			array('path, title, keywords, description, redirect, url', 'length', 'max'=>255, 'encoding'=>'UTF-8'),
            array('theme', 'length', 'max'=>50),
            array('language', 'length', 'max'=>10),
            array('alias', 'length', 'max'=>64, 'encoding'=>'UTF-8'),
            array('alias', 'match', 'pattern'=>'/^'.Yii::app()->params['aliasPattern'].'$/u'),
            array('url', 'PageUrlValidator'),
            array('access', 'safe'),
            array('author_id', 'safe'),
		);
        if ($this->virtual) {
            $rules[] = array('parent_id', 'unsafe');
        }
        if ($this->id==1) {
            $rules[] = array('alias, url', 'unsafe');
        }
		return $this->localizedRules($rules);
	}

    public function i18n()
    {
        return array(
            'title', 'keywords', 'description', 'alias', 'url',
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
            'author'=>array(self::BELONGS_TO, 'User', 'author_id'),
            'lastEditor'=>array(self::BELONGS_TO, 'User', 'editor_id'),
		);
	}

	public function scopes()
	{
		return array(
            'order' => array(
                'order'=>'`order`',
            ),
            'real' => array(
                'condition'=> '`virtual` IS NULL or `virtual` != 1',
            ),
		);
	}

    // Чем меньше операций, тем лучше быстродействие
    public function operations()
    {
        return array(
            'create'=>array(
                'label'=>'Add child page', // Право создавать дочернюю страницу в любом месте или только для этой страницы
                'defaultRoles'=>array('administrator', 'editor', 'author'),
            ),
            'read'=>array(
                'label'=>'View page', // Право просматривать все страницы или только эту
                'defaultRoles'=>array('anybody'),
            ),
            'updateContent'=>array(
                'label'=>'Edit page content', // Право редактировать контент всех страниц или только этой
                'defaultRoles'=>array('administrator', 'editor'),
            ),
            'updateSettings'=>array(
                'label'=>'Edit page settings', // Право редактировать свойства всех страниц или только этой
                'defaultRoles'=>array('administrator', 'editor'),
            ),
            'updateAccess'=>array(
                'label'=>'Edit page access', // Право редактировать права доступа всех страниц или только этой
                'defaultRoles'=>array('administrator'),
            ),
            'move'=>array(
                'label'=>'Move page', // Право перемещать все страницы
                'defaultRoles'=>array('administrator', 'editor'),
            ),
            'delete'=>array(
                'label'=>'Delete page', // Право удалять все страницы или только эту
                'defaultRoles'=>array('administrator', 'editor'),
            ),
        );
    }

    public function tasks()
    {
        return array(
            'createOnOwn'=>array(
                'label'=>'Add child page for own page',
                'bizRule'=>'return Yii::app()->user->id==$params["page"]->author_id;',
                'children'=>array('createPage'),
                'defaultRoles'=>array('author'),
            ),
            'readOwn'=>array(
                'label'=>'View own page',
                'bizRule'=>'return Yii::app()->user->id==$params["page"]->author_id;',
                'children'=>array('readPage'),
                'defaultRoles'=>array('author', 'authenticated'),
            ),
            'updateContentOwn'=>array(
                'label'=>'Edit own page content',
                'bizRule'=>'return Yii::app()->user->id==$params["page"]->author_id;',
                'children'=>array('updateContentPage'),
                'defaultRoles'=>array('author', 'authenticated'),
            ),
            'updateSettingsOwn'=>array(
                'label'=>'Edit own page settings',
                'bizRule'=>'return Yii::app()->user->id==$params["page"]->author_id;',
                'children'=>array('updateSettingsPage'),
                'defaultRoles'=>array('author'),
            ),
            'updateAccessOwn'=>array(
                'label'=>'Edit own page access',
                'bizRule'=>'return Yii::app()->user->id==$params["page"]->author_id;',
                'children'=>array('updateAccessPage'),
                'defaultRoles'=>array('author'),
            ),
            'deleteOwn'=>array(
                'label'=>'Delete own page',
                'bizRule'=>'return Yii::app()->user->id==$params["page"]->author_id;',
                'children'=>array('deletePage'),
                'defaultRoles'=>array('author'),
            ),
        );
    }

// 'bizRule'=>'return !empty(array_intersect(Yii::app()->user->roles, $params["page"]->access["{operation}Roles"])) || in_array(Yii::app()->user->id, $params["page"]->access["{operation}Users"]);',

    public function cacheVaryBy()
    {
        return array(
            'language'=>Yii::app()->language,
        );
    }

    public function childrenPages($id=0)
    {
        if ($id == 0)
            $id = $this->id;
        $this->getDbCriteria()->mergeWith(array(
            'condition'=>'parent_id = :id',
            'params'=>array(
                ':id' => $id
            ),
        ));
        return $this;
    }

	public function _getUnits($area='', $exclude=false)
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

	public function getUnits($area='', $exclude=false)
	{
        if ($this->_units == null) {
            $this->_units = PageUnit::model()->findAll(array(
                'condition' => 'page_id = :id',
                'params' => array(
                    'id' => $this->id,
                ),
                'with' => array('unit'),
                'order' => '`area`, `order`'
            ));
        }
        $units = array();
		if (!is_array($area)) {
			$area = array($area);
		}
        foreach ($this->_units as $unit) {
            if ($area) {
                if (!$exclude) {
                    if (in_array($unit->area, $area)) $units[] = $unit;
                } else {
                    if (!in_array($unit->area, $area)) $units[] = $unit;
                }
            } else $units[] = $unit;
        }
        return $units;
	}


	public static function getTree($exclude=array(),$exclude_children=true,$exclude_virtual=false)
	{
		$criteria['order'] = '`order`';
        $criteria['condition'] = '1';
		if (!empty($exclude))
		{
			if (!is_array($exclude)) $exclude = array($exclude);
			$criteria['condition'] .= ' AND `id` NOT IN ('.implode(',',$exclude).')';
		}
        if ($exclude_virtual) {
            $criteria['condition'] .= ' AND (`virtual` IS NULL or `virtual` != 1)';
        }
		$pages = Page::model()->localized()->getAll($criteria);
		$tree = array();
		foreach ($pages as $page) {
			if ($exclude_children && !empty($exclude))
			{
				$ids = explode(',',$page['path']);
				$intersect = array_intersect($exclude, $ids);
				if (!empty($intersect)) continue;
			}
			$tree[$page['path']][] = $page;
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

    public function save($runValidation = true, $attributes = null)
    {
        if ($this->id) {
            $oldThis = Page::model()->findByPk($this->id);
        } else {
            $oldThis = new Page();
        }
        $langs = array_keys(self::getLangs());
        foreach ($langs as $lang) {
            $param = $lang.'_url';
            $this->_url[$lang] = $oldThis->$param;
            $newurl = $this->generateUrl(true, $lang.'_alias');
            if ($this->_url[$lang] != $newurl) {
                $this->$param = $newurl;
            }
        }
        return parent::save($runValidation, $attributes);
    }

    public function beforeSave()
	{
		$this->_path = $this->path;
		$newpath = $this->generatePath(true);
		if ($this->_path != $newpath) {
			$this->path = $newpath;
		}
		return parent::beforeSave();
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
        $langs = array_keys(self::getLangs());
        foreach ($langs as $lang) {
            $param = $lang.'_url';
    		if ($this->_url[$lang] != $this->$param)
        	{
            	$children = $this->children;
                foreach ($children as $page) {
                    $page->$param = $page->generateUrl(true, $lang.'_alias');
    				$page->save(false);
        		}
            }
        }
		return parent::afterSave();
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
				call_user_func(array($tmp_class, 'model'))->deleteAll('unit_id = ' . intval($pu['id']));
			}
			$sql = 'DELETE FROM `' . Unit::tableName() . '` WHERE `id` IN (' . implode(',',$ids) . ')';
			Yii::app()->db->createCommand($sql)->execute();
		}
        return parent::afterDelete();
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

    public function generateUrl($full=false, $param='alias')
    {
		$ret = array();
		if ($this->parent_id > 0) {
			if ($full) {
				$ret[] = $this->parent->generateUrl($full, $param);
			} else
				$ret[] = $this->parent->$param;
			$ret[] = $this->$param;
		}
		return implode('/',$ret);
    }

	public function attributeLabels()
	{
		return array(
			'id' => Yii::t('cms', 'ID'),
			'parent_id' => Yii::t('cms', 'Parent page'),
			'title' => Yii::t('cms', 'Title'),
			'keywords' => Yii::t('cms', 'Keywords'),
			'description' => Yii::t('cms', 'Description'),
			'order' => Yii::t('cms', 'Order'),
			'active' => Yii::t('cms', 'Active'),
            'redirect' => Yii::t('cms', 'Redirect'),
            'theme' => Yii::t('cms', 'Page graphic theme'),
            'language' => Yii::t('cms', 'Page language'),
            'alias' => Yii::t('cms', 'Page alias'),
            'url' => Yii::t('cms', 'Page url'),
            'access'=> Yii::t('cms', 'Access rights'),
            'author_id'=>Yii::t('cms', 'Author'),
		);
	}

    public function scheme()
    {
        return array(
            'id' => 'pk',
            'parent_id' => 'integer unsigned',
            'path' => 'string',
            'title' => 'string',
            'keywords' => 'string',
            'description' => 'string',
            'order' => 'integer unsigned',
            'active' => 'boolean',
            'redirect' => 'string',
            'theme' => 'char(32)',
            'language' => 'char(32)',
            'alias' => 'char(64)',
            'url'=>'string',
            'virtual'=>'boolean',
            'author_id'=>'integer unsigned',
            'editor_id'=>'integer unsigned',
            'create'=>'datetime',
            'modify'=>'datetime',
            'access'=>'text',
        );
    }

    public function install()
    {
        $obj = new self;
        $obj->title = Yii::t('cms', 'Homepage');
        $langs = array_keys(I18nActiveRecord::getLangs(Yii::app()->language));
        foreach ($langs as $lang) {
            $obj->{$lang.'_title'} = Yii::t('cms', 'Homepage', array(), null, $lang);
        }
        $obj->active = true;
        $obj->author_id = User::getAdmin()->id;
        $obj->create = new CDbExpression('NOW()');
        $obj->parent_id = 0;
        $obj->order = 0;
        $obj->save(false);
    }
	
	public static function form()
	{
		return array(
//			'title'=>Yii::t('cms', 'Page properties'),
			'elements'=>array(
                Form::tab(Yii::t('cms', 'Page properties')),
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
                    'canClear'=>false,
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
                Form::tab(Yii::t('cms', 'URL')),
                'alias'=>array(
                    'type'=>'text',
                    'maxlength'=>64,
                    'size'=>32,
                ),
                'url'=>array(
                    'type'=>'text',
                    'readonly'=>true,
                    'size'=>55,
                ),
                Form::tab(Yii::t('cms', 'Extra')),
                'redirect'=>array(
                    'type'=>'Link',
                    'showFileManagerButton'=>false,
                    'showUploadButton'=>false
                ),
                'theme'=>array(
                    'type'=>'ThemeSelect',
                ),
                'language'=>array(
                    'type'=>'LanguageSelect'
                ),
                Form::tab(Yii::t('cms', 'Access rights')),
                'author_id'=>array(
                    'type'=>'ComboBox',
                    'array'=>CHtml::listData(User::model()->findAll(), 'id', 'fullname'),
                ),
                'access'=>array(
                    'type'=>'AccessRights',
                ),
			),
			'buttons'=>array(
				'save'=>array(
					'type'=>'submit',
					'label'=>Yii::t('cms', 'Save'),
					'title'=>Yii::t('cms', 'Save and close window')
				),
			)
		);
	}

    public function behaviors()
    {
        return array(
            'CTimestampBehavior' => array(
                'class' => 'zii.behaviors.CTimestampBehavior',
                'createAttribute' => 'create',
                'updateAttribute' => 'modify',
            ),
            'CSerializeBehavior' => array(
                'class' => 'application.behaviors.CSerializeBehavior',
                'serialAttributes' => array('access'),
            ),
        );
    }

    public function isSimilarTo($page, $areas=array(), $unitId=0)
    {
        $id = is_object($page) ? $page->id : $page;
        $sql = 'SELECT `unit_id` FROM `' . PageUnit::tableName() . '`
                WHERE `page_id` = :id
                      AND `area` ' . (!empty($areas) ? (is_array($areas) ? 'IN ('.implode(',',$areas).')' : ' = `area`') : 'NOT LIKE "main%"').'
                      '.($unitId ? ' AND `unit_id` != '.intval($unitId) : '');
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
        $d = date("Y-m-d H:i:s");
		$obj->title = Yii::t('cms', 'New page createad at {time}', array('{time}' => $d));
        $obj->alias = self::sanitizeAlias(date("Ymd His"));
        $obj->url = '/'.$obj->alias;
        $langs = array_keys(I18nActiveRecord::getLangs(Yii::app()->language));
        foreach ($langs as $lang) {
            $obj->{$lang.'_title'} = Yii::t('cms', 'New page createad at {time}', array('{time}' => $d), null, $lang);
            $obj->{$lang.'_alias'} = self::sanitizeAlias(date("Ymd His"));
            $obj->{$lang.'_url'} = '/'.$obj->{$lang.'_alias'};
        }
		$obj->active = true;
		$obj->order = 0;
		return $obj;
	}

    public static function sanitizeAlias($str)
    {
        $pattern = Yii::app()->params['aliasPattern'];
        $pattern = '[^' . substr($pattern,1,-1);
        $str = str_replace(array(' ', ':', '.'), '-', $str);
        $str = preg_replace('/'.$pattern.'/u', '', $str);
        while (strpos($str,'--')!==false) {
            $str = str_replace('--', '-', $str);
        }
		if(function_exists('mb_strlen'))
			$str=mb_substr($str,0,64,'UTF-8');
		else
			$str=substr($str,0,64);
        return $str;
    }

    public static function transliterate($str)
    {
        $ret = $str;
        $transliteration = Page::transliteration();
        if (is_array($transliteration) && !empty($transliteration)) {
            $ret = str_replace($transliteration[0], $transliteration[1], $str);
        }
        return $ret;

    }

    public static function transliteration()
    {
        $files = array(
            Yii::getPathOfAlias('config.transliteration').'.php',
            Yii::getPathOfAlias('application.config.transliteration').'.php',
        );
        foreach ($files as $file) {
            if (is_file($file))
                return include($file);
        }
        return false;
    }

}