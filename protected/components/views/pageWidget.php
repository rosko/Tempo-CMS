    <?php
    if(!call_user_func(array($widgetClass, 'cacheable')) || $this->beginCache(serialize($cacheVaryBy), $properties)) {
        $content = $pageWidget->widget->content;
        if ($content) { ?>

<div
    <?php if ($editArea) { ?>title="<?php echo call_user_func(array($widgetClass, 'name')); ?>"<?php } ?>
    id="cms-pagewidget-<?=$pageWidget->id?>"
    class="<?php if ($editArea) { ?>cms-pagewidget <? } ?>pagewidget cms-widget-<?=$pageWidget->widget->class?>"
    rel="<?=$pageWidget->widget->class?>"
    rev="<?=$pageWidget->widget->id?>"
    content_id="<?=$content->id?>"
>

    <?=$content->widget($widgetClass, array('pageWidget'=>$pageWidgets));?>
</div>

    <?php
        }
        if (call_user_func(array($widgetClass, 'cacheable')))
            $this->endCache();
    } ?>