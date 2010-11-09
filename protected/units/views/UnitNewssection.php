<h2><?=$unit->title?></h2>
<?php
    $items = UnitNewsitem::model()
                ->public()
                ->selectPage($content->pageNumber, $content->per_page)
                ->findAll('newssection_id = :id', array(':id'=>$content->id));

    $pager = $content->renderPager(count($items), $content->itemsCount, $content->pageNumber, $content->per_page);
    echo $pager;

    foreach ($items as $item)
    {
            Yii::app()->controller->renderPartial('application.units.views.UnitNewsitem',
                          array('unit'=>$item->unit,
                                'content'=>$item,
                                'pageunit'=>$pageunit,
                                'in_section'=>true));
    }

    if (count($items)>=5) {
        echo $pager;
    }

?>