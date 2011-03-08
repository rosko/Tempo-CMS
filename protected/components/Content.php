<?php
class Content extends I18nActiveRecord
{
    const EXCLUSIVE = false; // Разрешает создавать только один экземпляр юнита и только в одном месте

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
                'label'=>Yii::t('cms', 'Template'),
            ),
        );
    }
    public function settingsRules()
    {
        return array(
            array('template', 'length', 'max'=>32),
        );
    }

    public function cacheParams()
    {
        return array();
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
            'local.units.' . $className . '.' . $viewFile,
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

    public function renderPager($showedCount, $itemCount, $currentPage, $pageSize=0, $page_id=0, $pagerCssClass='')
    {
        if ($showedCount < $itemCount) {
            foreach ($_GET as $k => $v) {
                if (substr($k,-5)=='_page' && !isset($_REQUEST[$k])) {
                    unset($_GET[$k]);
                }
            }
            $pagination = new CPagination($itemCount);
            if ($pageSize < 1)
                $pageSize = Yii::app()->settings->getValue('defaultsPerPage');
            $pagination->pageVar = $this->getPageVar();
            $pagination->pageSize = $pageSize;
            $pagination->currentPage = $currentPage-1;
            $pagination->route = 'page/view';
            $params = $_GET;
            if (Yii::app()->controller->action->id == 'unitView') {
                unset($params['pageunit_id']);
                unset($params['_']);
            }
            $pagination->params = array_merge($params, array(
                'id' => Yii::app()->controller->_model->id,
                'alias' => Yii::app()->controller->_model->alias,
                'url' => Yii::app()->controller->_model->url,
            ));
            $pagerCssClass .= Yii::app()->settings->getValue('ajaxPager') ? ' ajaxPager ' : '';
            return Yii::app()->controller->widget('CLinkPager', array(
                'pages'=>$pagination,
                'htmlOptions'=>array(
                    'class'=> 'yiiPager ' . $pagerCssClass,
                ),
                'maxButtonCount'=>5), true);
        }
    }

    public function getUnitPageArray()
    {
        $sql = 'SELECT p.* FROM `'.Page::tableName().'`  as p INNER JOIN `' . PageUnit::tableName() . '` as pu ON pu.page_id = p.id WHERE pu.unit_id = :unit_id ORDER BY pu.id LIMIT 1';
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(':unit_id', $this->unit_id, PDO::PARAM_INT);
        $page = $command->queryRow();
        $page['alias'] = $page[Yii::app()->language.'_alias'];
        $page['url'] = $page[Yii::app()->language.'_url'];
        return $page;
    }

    public function getUnitUrl($absolute=false)
    {
        $page = $this->getUnitPageArray();
        if ($absolute)
            return Yii::app()->controller->createAbsoluteUrl('page/view', array('id'=>$page['id'], 'alias'=>$page['alias'], 'url'=>$page['url']));
        else
            return Yii::app()->controller->createUrl('page/view', array('id'=>$page['id'], 'alias'=>$page['alias'], 'url'=>$page['url']));
    }

    public function prepare($params)
    {
        $params['className'] = get_class($this);
        $params['unit'] = $this->unit;
        $params['content'] = $this;
        $params['isGuest'] = Yii::app()->user->isGuest;
        if (!$params['isGuest']) {
            $params['user'] = User::model()->findByPk(Yii::app()->user->id);
        }
        $params['page'] = Yii::app()->controller->loadModel();
        $params['editMode'] = Yii::app()->user->checkAccess('updatePage');
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

        $output = '';
        if ($params['editMode'] && method_exists($this, 'form') && $arr = $this->form()) {
            if (is_array($arr))
            foreach ($arr['elements'] as $i => $elem) {
                if (is_array($elem) && $elem['type']=='RecordsGrid') {
                    $output .= Yii::app()->controller->renderPartial('application.components.inputs.views.RecordsGrid', array(
                        'id' => __CLASS__.'_'.get_class($this).'_'.$this->id,
                        'foreign_attribute' => $elem['foreign_attribute'],
                        'addButtonTitle' => $elem['addButtonTitle'],
                        'page_id' => $params['page']->id,
                        'area' => $params['pageunit']->area,
                        'type' => Unit::getUnitTypeByClassName($elem['class_name']),
                        'class_name' => $elem['class_name'],
                        'section_id' => $this->id,
                        'section_type' => get_class($this),
                        'pageunit_id' => $params['pageunit']->id,
                        'unit_id' => $params['unit']->id,
                    ), true);
                }
            }
            if ($output) {
                $output .= '<hr />';
            }
        }

        $output2 = '';
        if ($params['editMode'])
        {
            if (method_exists($params['content'], 'resizableObjects')) {
                $output2 .= Yii::app()->controller->renderPartial('application.components.views.resizable', $params, true);
            }
        }

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
            $aliases[] = $s . '.unit';

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

        $output .= Yii::app()->controller->renderPartial($alias,
                           $params, true);
        if (trim($output) == '' && $params['editMode'])  {
            $output = Yii::t('cms', '[Unit "{name}" is empty on this page] - this messages showed in edit mode only', array('{name}' => call_user_func(array($className, 'name'))));
        }

        if ($return)
            return $output . $output2;
        else
            echo $output . $output2;
        
    }

    public function getTemplateDirAliases($className='')
    {
        if ($className == '')
            $className = get_class($this);
        $pathes = array(
            'application.units.'.$className.'.templates',
            'local.units.'.$className.'.templates',
            'local.templates.'.$className,
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
        $attr = $this->getI18nFieldName($attr);
        $sql = "SELECT DISTINCT `{$attr}` FROM `" . $this->tableName() . "` ORDER BY `{$attr}` ASC";
        return Yii::app()->db->createCommand($sql)->queryColumn();
    }

    // Обработка ajax-запроса
    public function ajax($vars)
    {
        $unit = Unit::model()->findByPk($vars['unit_id']);
        $content = $unit->content;
        if ($content && Yii::app()->user->checkAccess('updatePage')) {
            if (isset($vars['Content'])) {
                $content->attributes=$vars['Content'];
            }
            if (isset($vars['attribute']) && isset($vars['width']) && isset($vars['height'])
                    && isset($vars['tag']) && isset($vars['number'])) {
                $html = $content->{$vars['attribute']};
                preg_match_all("/<{$vars['tag']}[^>]*?\/?>/msiu", $html, $matches, PREG_OFFSET_CAPTURE);
                $t = $matches[0][intval($vars['number'])];
                $source = $t[0];
                $repl = preg_replace("/width=[\"\']?([\d]*)[\"\'?]/msi", 'width="'.intval($vars['width']).'"', $t[0]);
                if ($repl == $t[0]) {
                    $repl = str_ireplace('<'.$vars['tag'], '<'.$vars['tag'].' width="'.intval($vars['width']).'"', $repl);
                }
                $t[0] = $repl;
                $repl = preg_replace("/height=[\"\']?([\d]*)[\"\'?]/msi", 'height="'.intval($vars['height']).'"', $repl);
                if ($repl == $t[0]) {
                    $repl = str_ireplace('<'.$vars['tag'], '<'.$vars['tag'].' height="'.intval($vars['height']).'"', $repl);
                }
                $content->{$vars['attribute']} = substr($html, 0, $t[1]) . str_replace($source, $repl, substr($html, $t[1], strlen($repl))) . substr($html, $t[1]+strlen($repl));
            }
            echo $content->save();
        }
    }
}
