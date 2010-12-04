<?php
$cs = Yii::app()->getClientScript();

if ($show_title === false) {
    $js = "$('#{$form->uniqueId} .field_title').hide();";
    $cs->registerScript('hide_title', $js, CClientScript::POS_READY);
} else {
    $js = "$('#{$form->uniqueId} .field_title').prependTo('#{$form->uniqueId} .ui-tabs-panel:eq(0)')";
    $cs->registerScript('move_title', $js, CClientScript::POS_READY);
}

$js = '';

if (Yii::app()->settings->getValue('showUnitAppearance') && $form['unit']->model) {
    $txtAppearance = Yii::t('cms', 'Appearance');
    $js .= <<<EOD

    if ($('#{$form->uniqueId} .field_template').length) {
        $('#{$form->uniqueId} ul.ui-tabs-nav a').each(function() {
            if ($(this).text() == '{$txtAppearance}') {
                $('#{$form->uniqueId} .field_template').appendTo($(this).attr('href'));
            }
        });
    }

EOD;

}

$js .= <<<EOD

    var ics = {
        go: 'ui-icon-arrowfresh-1-s',
        refresh: 'ui-icon-refresh',
        deletepage: 'ui-icon-closethick',
        save: 'ui-icon-check',
        apply: 'ui-icon-bullet'
    };
    $('input[type=submit]').button();

EOD;

if ($js)
    $cs->registerScript('form', $js, CClientScript::POS_READY);
?>
<div class="form" id="<?=$form->uniqueId?>">
<?php if ($caption) { ?>
    <div class="cms-caption">
        <img style="float:left;margin-right:1em;" valign="baseline" src="<?=$caption['icon']?>" />
        <h2><?=$caption['label']?></h2>
    </div>
<?php } ?>
<?=$form?>
</div>