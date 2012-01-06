    <?php
    if(!call_user_func(array($className, 'cacheable')) || $this->beginCache(serialize($cacheVaryBy), $properties)) {
        $content = $pageUnit->unit->content;
        if ($content) { ?>

<div
    <?php if ($editArea) { ?>title="<?php echo call_user_func(array($className, 'unitName')); ?>"<?php } ?>
    id="cms-pageunit-<?=$pageUnit->id?>"
    class="<?php if ($editArea) { ?>cms-pageunit <? } ?>pageunit cms-unit-<?=$pageUnit->unit->type?>"
    rel="<?=$pageUnit->unit->type?>"
    rev="<?=$pageUnit->unit->id?>"
    content_id="<?=$content->id?>"
>
    <?=$content->widget($className, array('pageUnit'=>$pageUnit));?>
</div>

    <?php
        }
        if (call_user_func(array($className, 'cacheable')))
            $this->endCache();
    } ?>