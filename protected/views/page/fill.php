<?php
$cs = Yii::app()->getClientScript();
$cs->registerScript('pagefill', <<<EOD
    $(function() {
    });
EOD
);
?>


<h3>Заполнить страницу на основании</h3>
<h4>Из областей</h4>
<input type="checkbox" checked name="area[]" id="area_top" value="top" /><label for="area_top"> top</label><br />
<input type="checkbox" checked name="area[]" id="area_main" value="main" /><label for="area_main"> main</label><br />
<input type="checkbox" checked name="area[]" id="area_right" value="right" /><label for="area_right"> right</label><br />
<h4>Со страницы</h4>
