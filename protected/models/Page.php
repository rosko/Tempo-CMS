<?php

class Page extends I18nActiveRecord
{
    protected $_path;
    protected $_url = array();
    protected $_widgets = null;

    public static function model($className = __CLASS__)
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
            array('parent_id, order, active', 'numerical', 'integerOnly' => true),
            array('path, title, keywords, description, redirect, url', 'length', 'max' => 255, 'encoding' => 'UTF-8'),
            array('theme', 'length', 'max' => 50),
            array('language', 'length', 'max' => 10),
            array('alias', 'length', 'max' => 64, 'encoding' => 'UTF-8'),
            array('alias', 'match', 'pattern' => '/^' . Yii::app()->params['aliasPattern'] . '$/u'),
            array('url', 'PageUrlValidator'),
            array('access', 'safe'),
            array('author_id', 'safe'),
        );
        if ($this->id == 1) {
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
            'parent' => array(self::BELONGS_TO, 'Page', 'parent_id'),
            'children' => array(
                self::HAS_MANY, 'Page', 'parent_id',
                'order' => '`order`'
            ),
            'childrenCount' => array(self::STAT, 'Page', 'parent_id'),
            'author' => array(self::BELONGS_TO, 'User', 'author_id'),
            'lastEditor' => array(self::BELONGS_TO, 'User', 'editor_id'),
        );
    }

    public function scopes()
    {
        return array(
            'order' => array(
                'order' => '`order`',
            ),
        );
    }

    /*
        public function cacheVaryBy()
        {
            return array(
                'language'=>Yii::app()->language,
            );
        }
    */
    public function childrenPages($id = 0)
    {
        if ($id == 0) {
            $id = $this->id;
        }
        $this->getDbCriteria()->mergeWith(
            array(
                'condition' => 'parent_id = :id',
                'params' => array(
                    ':id' => $id
                ),
            )
        );
        return $this;
    }

    public function _getWidgets($area = '', $exclude = false)
    {
        $condition = '`t`.`page_id` = :id';
        $params = array(':id' => $this->id);
        if (!is_array($area)) {
            $area = array($area);
        }
        if ($area) {
            foreach ($area as $i => $ar) {
                $sign = $exclude ? '<>' : '=';
                $condition .= " AND (`t`.`area` {$sign} :area{$i})";
                $params[':area' . $i] = $ar;
            }
        }

        return PageWidget::model()->findAll(
            array(
                'condition' => $condition,
                'with' => array('widget'),
                'order' => '`t`.`area`, `t`.`order`',
                'params' => $params
            )
        );
    }

    public function getWidgets($area = '', $exclude = false)
    {
        if ($this->_widgets == null) {
            $this->_widgets = PageWidget::model()->findAll(
                array(
                    'condition' => 'page_id = :id',
                    'params' => array(
                        'id' => $this->id,
                    ),
                    'with' => array('widget'),
                    'order' => '`area`, `order`'
                )
            );
        }
        $widgets = array();
        if (!is_array($area)) {
            $area = array($area);
        }
        foreach ($this->_widgets as $widget) {
            if ($area) {
                if (!$exclude) {
                    if (in_array($widget->area, $area)) {
                        $widgets[] = $widget;
                    }
                } else {
                    if (!in_array($widget->area, $area)) {
                        $widgets[] = $widget;
                    }
                }
            } else {
                $widgets[] = $widget;
            }
        }
        return $widgets;
    }


    public static function getTree($exclude = array(), $exclude_children = true)
    {
        $criteria['order'] = 't.`order`';
        $criteria['condition'] = '1';
        if (!empty($exclude)) {
            if (!is_array($exclude)) {
                $exclude = array($exclude);
            }
            $criteria['condition'] .= ' AND t.`id` NOT IN (' . implode(',', $exclude) . ')';
        }
        Page::model()->setPopulateMode(false);
        $pages = Page::model()->allowed('read')->findAll($criteria);
        Page::model()->setPopulateMode(true);
        $tree = array();
        foreach ($pages as $page) {
            if ($exclude_children && !empty($exclude)) {
                $ids = explode(',', $page['path']);
                $intersect = array_intersect($exclude, $ids);
                if (!empty($intersect)) {
                    continue;
                }
            }
            $tree[$page['path']][] = $page;
        }
        return $tree;
    }

    public function selectPage($number, $per_page = 0)
    {
        if ($per_page < 1) {
            $per_page = Yii::app()->settings->getValue('defaultsPerPage');
        }

        $offset = ($number - 1) * $per_page;
        if ($offset < 0) {
            $offset = 0;
        }
        $this->getDbCriteria()->mergeWith(
            array(
                'limit' => $per_page,
                'offset' => $offset
            )
        );
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
            $param = $lang . '_url';
            $this->_url[$lang] = $oldThis->$param;
            $newurl = $this->generateUrl(true, $lang . '_alias');
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
        if ($this->_path != $this->path) {
            $children = $this->children;
            foreach ($children as $page) {
                $page->path = $page->generatePath(true);
                $page->save(false);
            }
        }
        $langs = array_keys(self::getLangs());
        foreach ($langs as $lang) {
            $param = $lang . '_url';
            if ($this->_url[$lang] != $this->$param) {
                $this->$param = $this->generateUrl(true, $lang . '_alias');
                $children = $this->children;
                foreach ($children as $page) {
                    $page->save(false);
                }
            }
        }
        return parent::afterSave();
    }

    public function afterDelete()
    {
        PageWidget::model()->deleteAll('page_id = :page_id', array(':page_id' => $this->id));

        // Удаляем все блоки, которые больше нигде не размещены
        $sql = 'SELECT `widget`.`id`, `widget`.`class`  FROM `' . Widget::tableName() . '` as `widget`
				LEFT JOIN `' . PageWidget::tableName() . '` as `pagewidget` ON (`widget`.`id` = `pagewidget`.`widget_id`)



		$pus = Yii::app()->db->createCommand($sql)->queryAll();
				WHERE `pagewidget`.`id` IS NULL';

        $ids = array();
        if ($pus && is_array($pus)) {
            foreach ($pus as $pu) {
                $ids[] = intval($pu['id']);
                $widgetClass = $pu['class'];
                $modelClass = call_user_func(array($widgetClass, 'modelClassName'));
                call_user_func(array($modelClass, 'model'))->deleteAll('widget_id = ' . intval($pu['id']));
            }
            $sql = 'DELETE FROM `' . Widget::tableName() . '` WHERE `id` IN (' . implode(',', $ids) . ')';
            Yii::app()->db->createCommand($sql)->execute();
        }
        return parent::afterDelete();
    }

    public function deleteWithChildren()
    {
        $children = $this->children;
        if ($children) {
            foreach ($children as $page) {
                $page->deleteWithChildren();
            }
        }
        $this->delete();
    }

    public function generatePath($full = false)
    {
        $ret = array();
        if ($this->parent_id == 0) {
            $ret[] = $this->parent_id;
        } else {
            if ($full) {
                $ret[] = $this->parent->generatePath($full);
            } else {
                $ret[] = $this->parent->path;
            }
            $ret[] = $this->parent_id;
        }
        return implode(',', $ret);
    }

    public function generateUrl($full = false, $param = 'alias')
    {
        $ret = array();
        if ($this->parent_id > 0) {
            if ($full) {
                $ret[] = $this->parent->generateUrl($full, $param);
            } else {
                $ret[] = $this->parent->$param;
            }
            $ret[] = $this->$param;
        }
        return implode('/', $ret);
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
            'access' => Yii::t('cms', 'Access rights'),
            'author_id' => Yii::t('cms', 'Author'),
        );
    }

    public function scheme()
    {
        return array(
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
            'url' => 'string',
            'author_id' => 'integer unsigned',
            'editor_id' => 'integer unsigned',
            'access' => 'text',
        );
    }

    public function install()
    {
        $obj = new self;
        $obj->title = Yii::t('cms', 'Homepage');
        $langs = array_keys(I18nActiveRecord::getLangs(Yii::app()->language));
        foreach ($langs as $lang) {
            $obj->{$lang . '_title'} = Yii::t('cms', 'Homepage', array(), null, $lang);
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
            'elements' => array(
                Form::tab(Yii::t('cms', 'Page properties')),
                'title' => array(
                    'type' => 'text',
                    'maxlength' => 255,
                    'size' => 60
                ),
                /*				'active'=>array(
                        'type'=>'checkbox'
                    ),*/
                'parent_id' => array(
                    'type' => 'PageSelect',
                    'canClear' => false,
                ),
                'keywords' => array(
                    'type' => 'textarea',
                    'rows' => 4,
                    'cols' => 40
                ),
                'description' => array(
                    'type' => 'textarea',
                    'rows' => 4,
                    'cols' => 40
                ),
                Form::tab(Yii::t('cms', 'URL')),
                'alias' => array(
                    'type' => 'text',
                    'maxlength' => 64,
                    'size' => 32,
                ),
                'url' => array(
                    'type' => 'text',
                    'readonly' => true,
                    'size' => 55,
                ),
                Form::tab(Yii::t('cms', 'Extra')),
                'redirect' => array(
                    'type' => 'Link',
                    'showFileManagerButton' => false,
                    'showUploadButton' => false
                ),
                'theme' => array(
                    'type' => 'ThemeSelect',
                ),
                'language' => array(
                    'type' => 'LanguageSelect'
                ),
                Form::tab(Yii::t('cms', 'Access rights')),
                'author_id' => array(
                    'type' => 'ComboBox',
                    'array' => CHtml::listData(User::model()->findAll(), 'id', 'fullname'),
                ),
            ),
            'buttons' => array(
                'save' => array(
                    'type' => 'submit',
                    'label' => Yii::t('cms', 'Save'),
                    'title' => Yii::t('cms', 'Save and close window')
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
            'access' => array(
                'class' => 'application.behaviors.AccessCBehavior',
                'operations' => CMap::mergeArray(
                    array(
                        'create' => Yii::t('cms', 'Create new page'),
                        'read' => Yii::t('cms', 'View page'),
                        'update' => Yii::t('cms', 'Edit page content'),
                        'delete' => Yii::t('cms', 'Delete page'),
                    ),
                    self::operationsOnAreas()
                ),
                'defaultRules' => array(
                    // Создавать новые страницы могут только пользователи с ролью administrator
                    'create' => array(
                        'User' => array(
                            array('roles', Role::ADMINISTRATOR),
                        ),
                    ),
                    // Просматривать страницы могут все пользователи
                    'read' => array(
                        'User' => array(
                            array('roles', Role::ANYBODY),
                        ),
                    ),
                    // Редактировать страницы могут все пользователи с ролью editor кроме пользователя с логином nobody
                    'update' => array(
                        'User' => array(
                            array('roles', Role::EDITOR),
                        ),
                    ),
                    // Удалять страницу могут только администраторы
                    'delete' => array(
                        'User' => array(
                            array('roles', Role::ADMINISTRATOR),
                        ),
                    ),
                ),
            ),
        );
    }

    public function isSimilarTo($page, $areas = array(), $widgetId = 0)
    {
        $id = is_object($page) ? $page->id : $page;
        $sql = 'SELECT `widget_id` FROM `' . PageWidget::tableName() . '`
                WHERE `page_id` = :id
                      AND `area` ' . (!empty($areas) ? (is_array($areas) ? 'IN (' . implode(',', $areas) . ')'
            : ' = `area`') : 'NOT LIKE "main%"') . '
                      ' . ($widgetId ? ' AND `widget_id` != ' . intval($widgetId) : '');
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
        $sql = 'SELECT * FROM `' . PageWidget::tableName() . '` WHERE `page_id` = :page_id AND `area` NOT LIKE "main%"';
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(':page_id', $this->parent_id, PDO::PARAM_INT);
        $pus = $command->queryAll();
        if ($pus && is_array($pus)) {
            $sql = 'INSERT INTO `' . PageWidget::tableName() . '` (`page_id`, `widget_id`, `order`, `area`) VALUES ';
            $sql_arr = array();
            foreach ($pus as $pu) {
                $sql_arr[]
                    = '(' . intval($this->id) . ', ' . intval($pu['widget_id']) . ', ' . intval($pu['order']) . ', "'
                    . $pu['area'] . '")';
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
        $obj->url = '/' . $obj->alias;
        $langs = array_keys(I18nActiveRecord::getLangs(Yii::app()->language));
        foreach ($langs as $lang) {
            $obj->{$lang . '_title'} = Yii::t('cms', 'New page createad at {time}', array('{time}' => $d), null, $lang);
            $obj->{$lang . '_alias'} = self::sanitizeAlias(date("Ymd His"));
            $obj->{$lang . '_url'} = '/' . $obj->{$lang . '_alias'};
        }
        $obj->active = true;
        $obj->order = 0;
        return $obj;
    }

    public static function sanitizeAlias($str)
    {
        $pattern = Yii::app()->params['aliasPattern'];
        $pattern = '[^' . substr($pattern, 1, -1);
        $str = str_replace(array(' ', ':', '.'), '-', $str);
        $str = preg_replace('/' . $pattern . '/u', '', $str);
        while (strpos($str, '--') !== false) {
            $str = str_replace('--', '-', $str);
        }
        if (function_exists('mb_strlen')) {
            $str = mb_substr($str, 0, 64, 'UTF-8');
        } else {
            $str = substr($str, 0, 64);
        }
        if (Yii::app()->settings->getValue('slugTransliterate')) {
            $str = self::transliterate($str);
        }
        if (Yii::app()->settings->getValue('slugLowercase')) {
            if (function_exists('mb_strtolower')) {
                $str = mb_strtolower($str, 'UTF-8');
            } else {
                $str = strtolower($str);
            }
        }
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
            Yii::getPathOfAlias('config.transliteration') . '.php',
            Yii::getPathOfAlias('application.config.transliteration') . '.php',
        );
        foreach ($files as $file) {
            if (is_file($file)) {
                return include($file);
            }
        }
        return false;
    }

    public static function operationsOnAreas($themeName=null)
    {
        $ret = array();
        $areas = ThemeHelper::getDefined('areas', $themeName);
        if (!empty($areas) && is_array($areas)) {
            foreach ($areas as $area) {
                $ret[self::areaOperation('read', $area)] = Yii::t('cms', 'View blocks on page area {area}', array('{area}' => $area));
                $ret[self::areaOperation('update', $area)] = Yii::t('cms', 'Manage blocks on page area {area}', array('{area}' => $area));
            }
        }
        return $ret;
    }

    public static function areaOperation($type, $area)
    {
        return $type . '_area_' . $area;
    }

}