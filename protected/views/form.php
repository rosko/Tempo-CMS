<?php
$cs = Yii::app()->getClientScript();

if ($showTitle === false) {
    $js = "$('#{$form->uniqueId} .field_title').hide();";
    $cs->registerScript('hide_title', $js, CClientScript::POS_READY);
} else {
    $js = "$('#{$form->uniqueId} .field_title').prependTo('#{$form->uniqueId} .ui-tabs-panel:eq(0)')";
    $cs->registerScript('move_title', $js, CClientScript::POS_READY);
}

if (get_class($form->model)=='Page') {
    $js = '';
    if ($form->model->id != 1) {
        if ($form->model->isNewRecord) {

            $jsA = '';
            $jsB = '';
            if (Yii::app()->settings->getValue('slugTransliterate')) {
                $jsA .= 'transliterate(';
                $jsB .= ')';
            }
            if (Yii::app()->settings->getValue('slugLowercase')) {
                $jsA .= 'strtolower(';
                $jsB .= ')';
            }

            $js .= <<<EOD
            $('#{$form->uniqueId} .field_url').hide();
            $('#{$form->uniqueId} #Page_title').bind('keyup change',function() {
                $('#{$form->uniqueId} #Page_alias').val({$jsA}sanitizeAlias($(this).val(){$jsB}));
                $('#{$form->uniqueId} #Page_url').val(makeUrl(sanitizeAlias($('#{$form->uniqueId} #Page_alias').val()), $('#{$form->uniqueId} #Page_url').val()));
            });
EOD;
            $langs = array_keys(I18nActiveRecord::getLangs(Yii::app()->language));
            foreach ($langs as $lang) {
                $js .= <<<EOD
                $('#{$form->uniqueId} #Page_{$lang}_title').bind('keyup change',function() {
                    $('#{$form->uniqueId} #Page_{$lang}_alias').val({$jsA}sanitizeAlias($(this).val(){$jsB}));
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
    if (!$('#{$form->uniqueId} .ui-tabs-panel').length) {
        if ($('#{$form->uniqueId}').height() > $(window).height()*0.65) {
            $('#{$form->uniqueId}').height(Math.ceil($(window).height()*0.65)).css({'overflow-y':'auto'});
        }
    }

EOD;

if ($js)
    $cs->registerScript('form', $js, CClientScript::POS_READY);
?>
<div class="form" id="<?=$form->uniqueId?>">
<?php if ($caption) { ?>
    <div class="cms-caption">
        <span class="cms-icon-big-<?=$caption['icon']?> cms-icon"></span>
        <h2><?=$caption['label']?></h2>
    </div>
<?php } ?>
<?=$form?>
</div>