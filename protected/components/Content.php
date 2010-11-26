<?php
class Content extends CActiveRecord
{
	public static function form()
	{
		return array();
    }

    public function relations()
    {
        return array(
            'unit' => array(self::BELONGS_TO, 'Unit', 'unit_id'),
        );
    }

    public function beforeSave()
    {
        if ($this->isNewRecord) 
        {
            if ($this->hasAttribute('date')) {
                $this->date = new CDbExpression('NOW()');
            }
        }
        return parent::beforeSave();
    }

    public function dependencies()
    {
        return array();
    }

    // Настройки всего типа юнитов
    public function settings($className)
    {
        return array(
            'template' => array(
                'type'=>'TemplateSelect',
                'className'=>$className,
                'label'=>'Шаблон',
            ),
        );
    }
    public function settingsRules()
    {
        return array(
            array('template', 'length', 'max'=>32),
        );
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

    public static function renderFile($className, $viewFile, $params=array())
    {
        $params['className'] = $className;
        $files = array(
            'application.units.' . $className . '.' . $viewFile,
            'webroot.units.' . $className . '.' . $viewFile,
        );
        foreach ($files as $file) {
            if (is_file(Yii::getPathOfAlias($file).'.php'))
        	return Yii::app()->controller->renderPartial($file,
                    $params, true);
        }
    }

    public function getPageVar()
    {
        return strtolower(substr(get_class($this),4)).$this->id.'_page';
    }

    public function getPageNumber()
    {
        return intval($_GET[$this->getPageVar()]);
    }

    public function renderPager($showedCount, $itemCount, $currentPage, $pageSize=0, $page_id=0)
    {
        if ($showedCount < $itemCount) {
            $pagination = new CPagination($itemCount);
            if ($pageSize < 1)
                $pageSize = Yii::app()->settings->getValue('defaultsPerPage');
            $pagination->pageVar = $this->getPageVar();
            $pagination->pageSize = $pageSize;
            $pagination->currentPage = $currentPage-1;
            if (Yii::app()->controller->action->id == 'unitView') {
                $pagination->route = 'page/view';
                $pagination->params = array(
                    'id' => Yii::app()->controller->_model->id
                );
            }
            return Yii::app()->controller->widget('CLinkPager', array(
                'pages'=>$pagination,
                'maxButtonCount'=>5), true);
        }
    }

    public function getUnitUrl()
    {
        $sql = 'SELECT page_id FROM `' . PageUnit::tableName() . '` WHERE unit_id = :unit_id ORDER BY id LIMIT 1';
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(':unit_id', $this->unit_id, PDO::PARAM_INT);
        $page_id = $command->queryScalar();
        return Yii::app()->controller->createUrl('page/view', array('id'=>$page_id));
    }

    public function prepare($params)
    {
        $params['className'] = get_class($this);
        $params['unit'] = $this->unit;
        $params['content'] = $this;
        $params['page'] = Yii::app()->controller->loadModel();
        $params['editMode'] = !Yii::app()->user->isGuest;
        $params['settings']['global'] = Yii::app()->settings->model->getAttributes();
        $len = strlen($params['className']);
        foreach ($params['settings']['global'] as $k => $v) {
            if (substr($k,0,$len+1) == $params['className'].'.') {
                $params['settings']['local'][substr($k,$len+1)] = $v;
            }

        }
        return $params;
    }

    public function run($params=array(), $return=false)
    {
        $params = $this->prepare($params);
        $className = Unit::getClassNameByUnitType($this->unit->type);
        $params['content'] = $params['content']->attributes;

        $aliases = array();
        $template = $params['unit']->template
                        ? basename($params['unit']->template)
                        : Yii::app()->settings->getValue($className.'.template');

        $dirs = $this->getTemplateDirAliases();
        if ($template)
            foreach ($dirs as $s)
                $aliases[] = $s . '.'. $template;
        foreach ($dirs as $s)
            $aliases[] = $s . '.'. $className;

        foreach ($aliases as $a) {
            if (Yii::app()->controller->getViewFile($a)!==false) {
                $alias = $a;
                break;
            }
        }
        if (!isset($alias)) return false;

        foreach ($params as $k => $v)
        {
            if ($v instanceof CModel)
                $params[$k] = $v->getAttributes();
        }

        $output = Yii::app()->controller->renderPartial($alias,
                           $params, true);
        if (trim($output) == '' && $params['editMode'])  {
            $output = '[Блок "'.$className::NAME.'" на этой странице пуст] - это сообщение отображается только в режиме редактирования';
        }
        if ($return)
            return $output;
        else
            echo $output;
        
    }

    public function getTemplateDirAliases($className='')
    {
        if ($className == '')
            $className = get_class($this);
        $pathes = array(
            'application.units.'.$className.'.templates',
            'webroot.units.'.$className.'.templates',
            'webroot.templates.'.$className,
        );
        return $pathes;
    }

    public function getTemplates($className='', $basenameOnly=true)
    {
        if ($className == '')
            $className = get_class($this);

		if((Yii::app()->getViewRenderer())!==null)
			$extension=Yii::app()->getViewRenderer()->fileExtension;
		else
			$extension='.php';

        $files = array();
        $pathes = self::getTemplateDirAliases($className);
        foreach ($pathes as $path) {
            $path = Yii::getPathOfAlias($path);
            if (is_dir($path))
                $files = array_merge($files, CFileHelper::findFiles($path, array(
                    'fileTypes' => array(substr($extension,1)),
                    'level' => 0,
                    'exclude' => array(
                        $className . $extension,
                     ),
                )));
            }
        $data = array();
        if ($files != array()) {
            //array_walk($files, 'basename');
            if ($basenameOnly) {
                foreach ($files as $k => $file) {
                    $files[$k] = basename($file, $extension);
                }
                $data = array_combine($files, $files);
            } else {
                $data = $files;
            }
        }

        return $data;
    }

    public function getAllValuesBy($attr)
    {
        $sql = "SELECT DISTINCT `{$attr}` FROM `" . $this->tableName() . "` ORDER BY `{$attr}` ASC";
        return Yii::app()->db->createCommand($sql)->queryColumn();
    }
}

?>