<?php
        $cs = Yii::app()->getClientScript();
        $cs->setCoreScriptUrl('/js/empty');
        if ($show_title === false) {
            $js = "$('.field_title').hide();";
            $cs->registerScript('hide_title', $js, CClientScript::POS_READY);
        }
?>
<div class="form">
<?=$form?>
</div>
