<?php
$ids = explode(',', $page->path);
$pages = Page::model()->findAll(array(
    'condition' => '`id` IN ('.$page->path.')',
    'order' => '`path` DESC'
                                        ));
$parents = array();
foreach ($pages as $p) {
    $parents[$p->id] = $p;
}
//unset($pages);

$links = array();
foreach ($ids as $id) {
    if ($id == 0 || $id == 1) continue;
    $links[$parents[$id]->title] = array('page/view', 'id'=>$parents[$id]->id);
}
if ($page->id != 1) 
    $links[] = $page->title;
else
    $links[] = '';

$separator = $content->separator ? ' ' . $content->separator . ' ' : ' &raquo; ';

$this->widget('zii.widgets.CBreadcrumbs', array(
    'separator' => $separator,
    'homeLink' => ($parents ? $parents[1]->title : $page->title),
    'links'=> $links
));

?>