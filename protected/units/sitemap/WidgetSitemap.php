<?php


class WidgetSitemap extends ContentWidget
{
    public function name($language=null)
    {
        return Yii::t('UnitSitemap.main', 'Sitemap', array(), null, $language);
    }

    public function icon()
    {
        return '/images/icons/fatcow/16x16/sitemap_color.png';
    }

    public function modelClassName()
    {
        return 'ModelSitemap';
    }

    public function unitClassName()
    {
        return 'UnitSitemap';
    }

    public function cacheVaryBy()
    {
        return array(
            'pageId' => Yii::app()->page->model->id,
        );
    }

    public function init()
    {
        parent::init();
        $model = $this->params['content']->page ? Page::model()->findByPk($this->params['content']->page) : $this->params['page'];
        $this->params['title'] = $this->params['widget']->title ? $this->params['widget']->title : $model->title;

        $id = $this->params['content']->recursive ? $model->id : $model->parent_id;
        $this->params['items'] = array();
        if ($id)
            $this->params['items'] = $this->getTree($id, $this->params, $this->params['content']->recursive, true);

        $this->params['count_items'] = count($this->params['items']);

        $this->params['pager'] = $this->renderPager(
                $this->params['count_items'],
                $model->childrenCount,
                $this->pageNumber,
                $this->params['content']->per_page
        );
        
    }

    public function getTree($id, $params, $recursive=0, $start=false)
    {
        Page::model()->setPopulateMode(false);
        if ($start)
            $items = Page::model()->order()->selectPage($this->pageNumber, $params['content']->per_page)->childrenPages($id)->findAll();
        else
            $items = Page::model()->order()->childrenPages($id)->findAll();
        Page::model()->setPopulateMode(true);
        if ($recursive > 1) {
            foreach ($items as $k => $item)
            {
                $items[$k]['children'] = $this->getTree($item['id'], $params, $recursive-1);
            }
        }
        return $items;
    }

    
}