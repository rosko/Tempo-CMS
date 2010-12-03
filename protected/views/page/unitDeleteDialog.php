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
                hideSplash();
                GetOutPageunitPanel();
                $('#cms-pageunit-{$pageunit_id}').remove();
            });
        }
        return false;
    });

    $('#cms-pageunit-delete-all').click(function() {
        if (confirm('{$txtAreYouSureDeleteEverywhere}'))
        {
            ajaxSave('/?r=page/unitDelete&pageunit_id=all&unit_id={$unit_id}', '', 'GET', function(ret) {
                hideSplash();
                GetOutPageunitPanel();
                $('.cms-pageunit[rev={$unit_id}]').remove();
            });
        }
        return false;
    });
    $('#cms-pageunit-delete-select').click(function() {
        pageunitSetDialog({$model->id}, {$pageunit_id}, {$unit_id});
        return false;
    });
    
});

EOD
);

?>

<h3><?=Yii::t('cms', 'Delete unit')?>:</h3>
<ul>
    <li><a class="cms-button" id="cms-pageunit-delete-this" href="#"><?=Yii::t('cms', 'On this page')?></a></li>
    <li><a class="cms-button" id="cms-pageunit-delete-all" href="#"><?=Yii::t('cms', 'On all pages')?></a></li>
    <li><a class="cms-button" id="cms-pageunit-delete-select" href="#"><?=Yii::t('cms', 'Select')?></a></li>
</ul>