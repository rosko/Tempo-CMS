<?php

class WidgetBlog extends ContentWidget
{
    public function icon()
    {
        return '/images/icons/fatcow/16x16/newspaper.png';
    }

    public function name($language=null)
    {
        return Yii::t('UnitBlog.main', 'Blog/news', array(), null, $language);
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

    public static function entryUrlParams($entry)
    {
        return array(
            'view' => $entry['id'] . '_'. Page::sanitizeAlias($entry['title'])
        );
    }

    public static function editParams()
    {
        if (Yii::app()->request->getQuery('view') !== null) {
            return array(
                'modelClass' => 'ModelBlog_Entry',
                'recordId' => intval(Yii::app()->request->getQuery('view')),
            );
        }
    }

    public function init()
    {
        parent::init();
        
        $viewParam = $this->urlParam('view');

        if (Yii::app()->request->getQuery($viewParam) !== null) {

            $this->params['url'] = $this->params['content']->getWidgetUrl(true);
            $this->params['entry'] = ModelBlog_Entry::model()->findByPk(intval(Yii::app()->request->getQuery($viewParam)));

            $this->params['templateType'] = 'entry';

        } else {

            $entries = ModelBlog_Entry::model()
                        ->public()
                        ->selectPage($this->pageNumber, $this->params['content']->per_page)
                        ->getAll('blog_id = :id', array(':id'=>$this->params['content']->id));

            $this->params['entries'] = array();
             foreach ($entries as $entry) {
                 $entry['url'] =  $this->params['content']->getWidgetUrl(true, self::entryUrlParams($entry));
                 $entry['image'] = unserialize($entry['image']);
                 $this->params['entries'][] = $entry;
            }        

            $this->params['pager'] = $this->renderPager(
                    count($entries),
                    $this->params['content']->itemsCount,
                    $this->pageNumber,
                    $this->params['content']->per_page);

        }

    }
        
}
