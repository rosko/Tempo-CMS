<?php
/**
 * PageComponent class file
 *
 * @author Alexey Volkov <a@insvit.com>
 * @link http://www.insvit.com/
 * @copyright Copyright &copy; 2010-2011 Alexey Volkov
 *
 */

/**
 * PageComponent - это класс компоненты приложения, которая отвечает за 
 */

class PageComponent extends CApplicationComponent
{
    protected $_model = null; // модель страницы

    /**
     * Возвращает объект запрашиваемой страницы
     * 
     * @return Page модель страницы
     */
    public function getModel()
    {
		if($this->_model===null)
		{
            if (!isset($_GET['pageId'])) 
            {
                $lang = Yii::app()->language;
                if (!empty($_GET['alias'])){
                    $page = Page::model()->find("`{$lang}_alias` = :alias", array(':alias'=> $_GET['alias']));
                    if ($page && (!Yii::app()->params['strictFind'] || $page[$lang.'_alias']==$_GET['alias'])) {
                        $_GET['pageId'] = $page->id;
                        $this->_model = $page;
                    }
                } elseif (!empty($_GET['url'])) {
                    $page = Page::model()->find("`{$lang}_url` = :url", array(':url'=> '/'.$_GET['url']));
                    if ($page && (!Yii::app()->params['strictFind'] || $page[$lang.'_url']=='/'.$_GET['url'])) {
                        $_GET['pageId'] = $page->id;
                        $this->_model = $page;
                    }
                } else {
                    $_GET['pageId'] = 1;
                    $this->_model = Page::model()->findByPk(1);
                }
            } 
            else
            {
                if(isset($_GET['pageId']))
                    $this->_model=Page::model()->findbyPk($_GET['pageId']);
                if($this->_model && $this->_model->language) {
                    Yii::app()->language = $this->_model->language;
                }
            }
		}
		return $this->_model;

    }

    public function setModel($model)
    {
        $this->_model = $model;
    }
    
}