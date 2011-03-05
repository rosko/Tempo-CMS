<?php

    $cs=Yii::app()->getClientScript();
    $txtAreYouSure = Yii::t('cms', 'Do you really want to delete this unit?');
    $txtAreYouSureDeleteEverywhere = Yii::t('cms', 'Do you really want to delete this unit everywhere?');
    $cs->registerScript('unitDeleteDialog', <<<EOD

$(function() {
    $('#cms-pageunit-delete-this').click(function() {
        if (confirm('{$txtAreYouSure}'))
        {
            ajaxSave('/?r=page/unitDelete&pageunit_id[]={$pageunit_id}&unit_id={$unit_id}', '', 'GET', function(ret) {
                closeDialog();
                $('#cms-pageunit-{$pageunit_id}').remove();
                CmsAreaEmptyCheck();
            });
        }
        return false;
    });

    $('#cms-pageunit-delete-all').click(function() {
        if (confirm('{$txtAreYouSureDeleteEverywhere}'))
        {
            ajaxSave('/?r=page/unitDelete&pageunit_id=all&unit_id={$unit_id}', '', 'GET', function(ret) {
                closeDialog();
                $('.cms-pageunit[rev={$unit_id}]').remove();
                CmsAreaEmptyCheck();
            });
        }
        return false;
    });
    $('#cms-pageunit-delete-select').click(function() {
        pageunitSetDialog({$model->id}, {$pageunit_id}, {$unit_id});
        return false;
    });
    $('.cms-buttons a').button().width('100%');
    
});

EOD
);

?>

<img style="float:left;margin-right:1em;" valign="baseline" src="/images/icons/fatcow/32x32/cross.png" />
<h3><?=Yii::t('cms', 'Delete unit')?>:</h3>
<ul class="cms-buttons">
    <li><a id="cms-pageunit-delete-this" href="#"><?=Yii::t('cms', 'On this page')?></a></li>
    <li><a id="cms-pageunit-delete-all" href="#"><?=Yii::t('cms', 'On all pages')?></a></li>
    <li><a id="cms-pageunit-delete-select" href="#"><?=Yii::t('cms', 'Select')?></a></li>
</ul>