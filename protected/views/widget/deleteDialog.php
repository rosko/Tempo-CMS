<?php

    $cs=Yii::app()->getClientScript();
    $txtAreYouSure = Yii::t('cms', 'Do you really want to delete this widget?');
    $txtAreYouSureDeleteEverywhere = Yii::t('cms', 'Do you really want to delete this widget everywhere?');
    $cs->registerScript('widgetDeleteDialog', <<<JS

$(function() {
    $('#cms-pagewidget-delete-this').click(function() {
        if (confirm('{$txtAreYouSure}'))
        {
            cmsAjaxSave('/?r=widget/delete&pageWidgetId[]={$pageWidgetId}&widgetId={$widgetId}', '', 'GET', function(ret) {
                cmsCloseDialog();
                $('#cms-pagewidget-{$pageWidgetId}').remove();
                cmsAreaEmptyCheck();
            });
        }
        return false;
    });

    $('#cms-pagewidget-delete-all').click(function() {
        if (confirm('{$txtAreYouSureDeleteEverywhere}'))
        {
            cmsAjaxSave('/?r=widget/delete&pageWidgetId=all&widgetId={$widgetId}', '', 'GET', function(ret) {
                cmsCloseDialog();
                $('.cms-pagewidget[rev={$widgetId}]').remove();
                cmsAreaEmptyCheck();
            });
        }
        return false;
    });
    $('#cms-pagewidget-delete-select').click(function() {
        cmsPageWidgetSetDialog({$model->id}, {$pageWidgetId}, {$widgetId});
        return false;
    });
    $('.cms-buttons a').button().width('100%');
    
});

JS
);

?>

<img style="float:left;margin-right:1em;" valign="baseline" src="/images/icons/fatcow/32x32/cross.png" />
<h3><?=Yii::t('cms', 'Delete widget')?>:</h3>
<div class="cms-buttons">
<a id="cms-pagewidget-delete-this" href="#"><?=Yii::t('cms', 'On this page')?></a><br /><br />
<a id="cms-pagewidget-delete-all" href="#"><?=Yii::t('cms', 'On all pages')?></a><br /><br />
<a id="cms-pagewidget-delete-select" href="#"><?=Yii::t('cms', 'Select')?></a>
</div>