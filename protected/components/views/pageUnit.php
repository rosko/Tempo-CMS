    <?php
    if(!call_user_func(array($widgetClass, 'cacheable')) || $this->beginCache(serialize($cacheVaryBy), $properties)) {
        $content = $pageUnit->unit->content;
        if ($content) { ?>

<div
    <?php if ($editArea) { ?>title="<?php echo call_user_func(array($widgetClass, 'name')); ?>"<?php } ?>
    id="cms-pageunit-<?=$pageUnit->id?>"
    class="<?php if ($editArea) { ?>cms-pageunit <? } ?>pageunit cms-unit-<?=$pageUnit->unit->class?>"
    rel="<?=$pageUnit->unit->class?>"
    rev="<?=$pageUnit->unit->id?>"
    content_id="<?=$content->id?>"
>

    <?=$content->widget($widgetClass, array('pageUnit'=>$pageUnit));?>
</div>

    <?php
        }
        if (call_user_func(array($widgetClass, 'cacheable')))
            $this->endCache();
    } ?>