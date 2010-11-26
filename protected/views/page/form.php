<?php
$cs = Yii::app()->getClientScript();

if ($show_title === false) {
    $js = "$('#{$form->uniqueId} .field_title').hide();";
    $cs->registerScript('hide_title', $js, CClientScript::POS_READY);
}

$js = '';

if (Yii::app()->settings->getValue('showUnitAppearance') && $form['unit']->model) {
    $js .= <<<EOD

    if ($('#{$form->uniqueId} .field_template').length) {
        $('#{$form->uniqueId} ul.ui-tabs-nav a').each(function() {
            if ($(this).text() == 'Внешний вид') {
                $('#{$form->uniqueId} .field_template').appendTo($(this).attr('href'));
            }
        });
    }

EOD;

}

if ($js)
    $cs->registerScript('form', $js, CClientScript::POS_READY);
?>
<div class="form" id="<?=$form->uniqueId?>">
<?=$form?>
</div>
