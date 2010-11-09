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
/*
    public function getUnit()
    {
		return Unit::model()->find('id=:id', array(':id'=>$this->unit_id));        
    }
*/
    public function dependencies()
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
        $file = 'application.units.views.' . $className . '.' . $viewFile;
        if (is_file(Yii::getPathOfAlias($file).'.php'))
		return Yii::app()->controller->renderPartial($file,
                $params, true);
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

}

?>