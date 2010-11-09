<?php
$model = $content->page_id ? Page::model()->findByPk($content->page_id) : $page;
$title = $unit->title ? $unit->title : $model->title;

$items = $content->recursive ? $model->order()->selectPage($content->pageNumber, $content->per_page)->childrenPages()->findAll()
                             : ($model->parent ? $model->parent->order()->selectPage($content->pageNumber, $content->per_page)->childrenPages()->findAll()
                                               : array());


$pagelist = UnitSitemap::renderPageList($items, $content->recursive-1, $content->length);

if ($pagelist != '') {
    if ($content->show_title)
        echo '<h3>'.$title.'</h3>';

    $pager = $content->renderPager(count($items), $model->childrenCount, $content->pageNumber, $content->per_page);
    echo $pager;

    echo $pagelist;

    if (count($items)>=10) {
        echo $pager;
    }
}


?>