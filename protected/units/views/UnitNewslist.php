<?php
if ($content->rule)
    eval("\$items = UnitNewsitem::model()->default()->{$content->rule}->findAll();");
if ($items) {
    foreach ($items as $item)
    {
        if (get_class($this) == 'Area') {
            $this->render('application.units.views.UnitNewsitem',
                          array('unit'=>$item->unit,
                                'content'=>$item));
        } else {
            $this->renderPartial('application.units.views.UnitNewsitem',
                          array('unit'=>$item->unit,
                                'content'=>$item));        
        }
    }
} else {
?>
<h2><?=UnitNewslist::NAME?>: пусто</h2>
<?php
}
?>
