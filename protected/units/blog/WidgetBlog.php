<?php

class WidgetBlog extends ContentWidget
{
    public function icon()
    {
        return '/images/icons/fatcow/16x16/newspaper.png';
    }

    public function name($language=null)
    {
        return Yii::t('UnitBlog.main', 'Blog/news section', array(), null, $language);
    }
    
    public function modelClassName()
    {
        return 'ModelBlog';
    }
    
    public function unitClassName()
    {
        return 'UnitBlog';
    }

    public function templates()
    {
        return array(
            'main' => Yii::t('UnitBlog.main', 'Main template'),
            'entry' => Yii::t('UnitBlog.main', 'Blog/news entry template'),
        );
    }
    
    public function urlParam($method)
    {
        return $method;
    }

    public static function urlParams()
    {
        return array('view','page');
    }

    public function init()
    {
        parent::init();
        
        $viewParam = $this->urlParam('view');
        
        if (empty($_GET[$viewParam])) {
            
            $entries= ModelBlog_Entry::model()
                        ->public()
                        ->selectPage($this->pageNumber, $this->params['content']->per_page)
                        ->getAll('blog_id = :id', array(':id'=>$this->params['content']->id));

            $urlPrefix = $this->params['content']->getWidgetUrl(true, array(
                 $viewParam => '',
            ));

            $this->params['entries'] = array();
             foreach ($entries as $entry) {
                 $entry['url'] =  $urlPrefix . $entry['id'] . '_'. Page::sanitizeAlias($entry['title']);
                 $this->params['entries'][] = $entry;
            }        

            $this->params['pager'] = $this->renderPager(
                    count($items),
                    $this->params['content']->itemsCount,
                    $this->pageNumber,
                    $this->params['content']->per_page);
            
        } else {
            
            $this->params['url'] = $this->params['content']->getWidgetUrl(true);
            $this->params['entry'] = ModelBlog_Entry::model()->findByPk(intval($_GET[$viewParam]));
            
            $this->template = 'entry';
            
        }
        
        
    }
        
}
