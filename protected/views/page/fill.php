<?php
$cs = Yii::app()->getClientScript();
$cs->registerScript('pagefill', <<<JS
    $(function() {
    });
JS
);
?>


<h3><?=Yii::t('cms', 'Fill page by')?></h3>
<h4><?=Yii::t('cms', 'By areas')?></h4>
<input type="checkbox" checked name="area[]" id="area_top" value="top" /><label for="area_top"> top</label><br />
<input type="checkbox" checked name="area[]" id="area_main" value="main" /><label for="area_main"> main</label><br />
<input type="checkbox" checked name="area[]" id="area_right" value="right" /><label for="area_right"> right</label><br />
<h4><?=Yii::t('cms', 'By page')?></h4>
