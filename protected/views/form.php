<?php
$cs = Yii::app()->getClientScript();

if (get_class($form->model)=='Page') {
    $js = '';
    if ($form->model->id != 1) {
        if ($form->model->isNewRecord) {

            $jsA = '';
            $jsB = '';
            if (Yii::app()->settings->getValue('slugTransliterate')) {
                $jsA .= 'cmsTransliterate(';
                $jsB .= ')';
            }
            if (Yii::app()->settings->getValue('slugLowercase')) {
                $jsA .= 'cmsStrToLower(';
                $jsB .= ')';
            }

            $js .= <<<JS
            $('#{$form->uniqueId} .field_url').hide();
            $('#{$form->uniqueId} #Page_title').bind('keyup change',function() {
                $('#{$form->uniqueId} #Page_alias').val({$jsA}cmsSanitizeAlias($(this).val(){$jsB}));
                $('#{$form->uniqueId} #Page_url').val(cmsMakeUrl(cmsSanitizeAlias($('#{$form->uniqueId} #Page_alias').val()), $('#{$form->uniqueId} #Page_url').val()));
            });
JS;
            $langs = array_keys(I18nActiveRecord::getLangs(Yii::app()->language));
            foreach ($langs as $lang) {
                $js .= <<<JS
                $('#{$form->uniqueId} #Page_{$lang}_title').bind('keyup change',function() {
                    $('#{$form->uniqueId} #Page_{$lang}_alias').val({$jsA}cmsSanitizeAlias($(this).val(){$jsB}));
                    $('#{$form->uniqueId} #Page_{$lang}_url').val(cmsMakeUrl(cmsSanitizeAlias($('#{$form->uniqueId} #Page_{$lang}_alias').val()), $('#{$form->uniqueId} #Page_url').val()));
                });
JS;
            }
        }
        $js .= <<<JS
        $('#{$form->uniqueId} #Page_alias').bind('keyup change',function() {
            $(this).val(cmsSanitizeAlias($(this).val()));
            $('#{$form->uniqueId} #Page_url').val(cmsMakeUrl(cmsSanitizeAlias($(this).val()), $('#{$form->uniqueId} #Page_url').val()));
        });
JS;
        $langs = array_keys(I18nActiveRecord::getLangs(Yii::app()->language));
        foreach ($langs as $lang) {
            $js .= <<<JS
            $('#{$form->uniqueId} #Page_{$lang}_alias').bind('keyup change',function() {
                $(this).val(cmsSanitizeAlias($(this).val()));
                $('#{$form->uniqueId} #Page_{$lang}_url').val(cmsMakeUrl(cmsSanitizeAlias($(this).val()), $('#{$form->uniqueId} #Page_{$lang}_url').val()));
            });
JS;
        }
    }
    $cs->registerScript('page_js', $js, CClientScript::POS_READY);

} else {

    if ($showTitle === false) {
        $js = "$('#{$form->uniqueId} .field_title').hide();";
        $cs->registerScript('hide_title', $js, CClientScript::POS_READY);
    } else {
        $langs = array_keys(I18nActiveRecord::getLangs(Yii::app()->language));
        if (empty($langs)) {
            $js = "$('#{$form->uniqueId} .field_title').prependTo('#{$form->uniqueId} .ui-tabs-panel:eq(0)')";
        } else {
            $js = "$('#{$form->uniqueId}_field_title').prependTo('#{$form->uniqueId} .ui-tabs-panel:eq(0)')";
        }
        $cs->registerScript('move_title', $js, CClientScript::POS_READY);
    }

}

$js = '';

if (Yii::app()->settings->getValue('showWidgetAppearance') && $form['widget']->model) {
    $txtAppearance = Yii::t('cms', 'Appearance');
    $js .= <<<JS

    if ($('#{$form->uniqueId} .field_template').length) {
        $('#{$form->uniqueId} ul.ui-tabs-nav a').each(function() {
            if ($(this).text() == '{$txtAppearance}') {
                $('#{$form->uniqueId} .field_template').appendTo($(this).attr('href'));
            }
        });
    }

JS;

}

$js .= <<<JS
    if (!$('#{$form->uniqueId} .ui-tabs-panel').length) {
        cmsDialogResize('#{$form->uniqueId}');
    }

JS;

if ($js)
    $cs->registerScript('form', $js, CClientScript::POS_READY);
?>
<div class="cms-form" id="<?=$form->uniqueId?>">
<?php if ($caption) { ?>
    <div class="cms-caption">
        <span class="cms-icon-big-<?=$caption['icon']?> cms-icon"></span>
        <h2><?=$caption['label']?></h2>
    </div>
<?php } ?>
<?=$form?>
</div>