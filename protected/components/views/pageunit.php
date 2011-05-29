    <?php
    if(!constant($className.'::CACHE') || $this->beginCache(serialize($cacheVaryBy), $properties)) {
        $content = $pageunit->unit->content;
        if ($content) { ?>

<div
    <?php if ($editArea) { ?>title="<?php echo call_user_func(array($className, 'unitName')); ?>"<?php } ?>
    id="cms-pageunit-<?=$pageunit->id?>"
    class="<?php if ($editArea) { ?>cms-pageunit <? } ?>pageunit cms-unit-<?=$pageunit->unit->type?>"
    rel="<?=$pageunit->unit->type?>"
    rev="<?=$pageunit->unit->id?>"
    content_id="<?=$content->id?>"
>
    <?=$content->run(array('pageunit'=>$pageunit));?>
</div>

    <?php
        }
        if (constant($className.'::CACHE'))
            $this->endCache();
    } ?>