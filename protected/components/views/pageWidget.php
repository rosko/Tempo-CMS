    <?php
    if(!call_user_func(array($widgetClass, 'cacheable')) || $this->beginCache(serialize($cacheVaryBy), $properties)) {
        $content = $pageWidget->widget->content;
        if ($content) { ?>

<div
    <?php if (!$readOnly) { ?>title="<?php echo call_user_func(array($widgetClass, 'name')); ?>"<?php } ?>
    id="cms-pagewidget-<?=$pageWidget->id?>"
    class="<?php if (!$readOnly) { ?>cms-pagewidget <? } ?>pagewidget cms-widget-<?=$pageWidget->widget->class?>"
    rel="<?=$pageWidget->widget->class?>"
    rev="<?=$pageWidget->widget->id?>"
    content_id="<?=$content->id?>"
    <?php
    if (method_exists($widgetClass, 'editParams')) {
        $params = call_user_func(array($widgetClass, 'editParams'));
        if (is_array($params)) {
            echo "data-edit-params='".CJSON::encode($params)."'";
        }
    }
    ?>
>

    <?=$content->widget($widgetClass, array('pageWidget'=>$pageWidget));?>
</div>

    <?php
        }
        if (call_user_func(array($widgetClass, 'cacheable')))
            $this->endCache();
    } ?>