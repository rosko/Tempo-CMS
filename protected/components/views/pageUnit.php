    <?php
    if(!constant($className.'::CACHE') || $this->beginCache(serialize($cacheVaryBy), $properties)) {
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
    <?=$content->run(array('pageUnit'=>$pageUnit));?>
</div>

    <?php
        }
        if (constant($className.'::CACHE'))
            $this->endCache();
    } ?>