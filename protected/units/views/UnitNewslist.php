<?php if ($unit->title) { ?>
<h3><?=$unit->title?></h3>
<?php } ?>
<?php
if ($content->rule)
    $content->rule .= '->';
eval("\$items = UnitNewsitem::model()->public()->{$content->rule}findAll();");
if ($items) {
    foreach ($items as $item)
    {
        Yii::app()->controller->renderPartial('application.units.views.UnitNewsitem',
                      array('unit'=>$item->unit,
                            'pageunit'=>$pageunit,
                            'content'=>$item));
    }
} else {
?>
<h2><?=UnitNewslist::NAME?>: пусто</h2>
<?php
}
?>
