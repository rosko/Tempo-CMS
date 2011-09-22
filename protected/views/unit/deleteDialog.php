<?php

    $cs=Yii::app()->getClientScript();
    $txtAreYouSure = Yii::t('cms', 'Do you really want to delete this unit?');
    $txtAreYouSureDeleteEverywhere = Yii::t('cms', 'Do you really want to delete this unit everywhere?');
    $cs->registerScript('unitDeleteDialog', <<<EOD

$(function() {
    $('#cms-pageunit-delete-this').click(function() {
        if (confirm('{$txtAreYouSure}'))
        {
            ajaxSave('/?r=unit/delete&pageUnitId[]={$pageUnitId}&unitId={$unitId}', '', 'GET', function(ret) {
                cmsCloseDialog();
                $('#cms-pageunit-{$pageUnitId}').remove();
                cmsAreaEmptyCheck();
            });
        }
        return false;
    });

    $('#cms-pageunit-delete-all').click(function() {
        if (confirm('{$txtAreYouSureDeleteEverywhere}'))
        {
            ajaxSave('/?r=unit/delete&pageUnitId=all&unitId={$unitId}', '', 'GET', function(ret) {
                cmsCloseDialog();
                $('.cms-pageunit[rev={$unitId}]').remove();
                cmsAreaEmptyCheck();
            });
        }
        return false;
    });
    $('#cms-pageunit-delete-select').click(function() {
        pageUnitSetDialog({$model->id}, {$pageUnitId}, {$unitId});
        return false;
    });
    $('.cms-buttons a').button().width('100%');
    
});

EOD
);

?>

<img style="float:left;margin-right:1em;" valign="baseline" src="/images/icons/fatcow/32x32/cross.png" />
<h3><?=Yii::t('cms', 'Delete unit')?>:</h3>
<div class="cms-buttons">
<a id="cms-pageunit-delete-this" href="#"><?=Yii::t('cms', 'On this page')?></a><br /><br />
<a id="cms-pageunit-delete-all" href="#"><?=Yii::t('cms', 'On all pages')?></a><br /><br />
<a id="cms-pageunit-delete-select" href="#"><?=Yii::t('cms', 'Select')?></a>
</div>