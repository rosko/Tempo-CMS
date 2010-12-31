<?php
$cs = Yii::app()->getClientScript();

if ($show_title === false) {
    $js = "$('#{$form->uniqueId} .field_title').hide();";
    $cs->registerScript('hide_title', $js, CClientScript::POS_READY);
} else {
    $js = "$('#{$form->uniqueId} .field_title').prependTo('#{$form->uniqueId} .ui-tabs-panel:eq(0)')";
    $cs->registerScript('move_title', $js, CClientScript::POS_READY);
}

if (get_class($form->model)=='Page') {
    $js = '';
    if ($form->model->id == 1) {
        $js .= <<<EOD
            $('#{$form->uniqueId} .field_alias').hide();
            $('#{$form->uniqueId} .field_url').hide();
EOD;
    } else {
        if ($form->model->isNewRecord) {
            $js .= <<<EOD
            $('#{$form->uniqueId} .field_url').hide();
            $('#{$form->uniqueId} #Page_title').bind('keyup change',function() {
                $('#{$form->uniqueId} #Page_alias').val(sanitizeAlias($(this).val()));
                $('#{$form->uniqueId} #Page_url').val(makeUrl(sanitizeAlias($('#{$form->uniqueId} #Page_alias').val()), $('#{$form->uniqueId} #Page_url').val()));
            });
EOD;
            $langs = array_keys(I18nActiveRecord::getLangs(Yii::app()->language));
            foreach ($langs as $lang) {
                $js .= <<<EOD
                $('#{$form->uniqueId} #Page_{$lang}_title').bind('keyup change',function() {
                    $('#{$form->uniqueId} #Page_{$lang}_alias').val(sanitizeAlias($(this).val()));
                    $('#{$form->uniqueId} #Page_{$lang}_url').val(makeUrl(sanitizeAlias($('#{$form->uniqueId} #Page_{$lang}_alias').val()), $('#{$form->uniqueId} #Page_url').val()));
                });
EOD;
            }
        }
        $js .= <<<EOD
        $('#{$form->uniqueId} #Page_alias').bind('keyup change',function() {
            $(this).val(sanitizeAlias($(this).val()));
            $('#{$form->uniqueId} #Page_url').val(makeUrl(sanitizeAlias($(this).val()), $('#{$form->uniqueId} #Page_url').val()));
        });
EOD;
        $langs = array_keys(I18nActiveRecord::getLangs(Yii::app()->language));
        foreach ($langs as $lang) {
            $js .= <<<EOD
            $('#{$form->uniqueId} #Page_{$lang}_alias').bind('keyup change',function() {
                $(this).val(sanitizeAlias($(this).val()));
                $('#{$form->uniqueId} #Page_{$lang}_url').val(makeUrl(sanitizeAlias($(this).val()), $('#{$form->uniqueId} #Page_{$lang}_url').val()));
            });
EOD;
        }
    }
    $cs->registerScript('page_js', $js, CClientScript::POS_READY);
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
    $('input[type=submit]').each(function() {
        $(this).button({icons: {primary: ics[$(this).attr('name')]} });
    });


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