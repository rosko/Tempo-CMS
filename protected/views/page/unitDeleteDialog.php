<?php

    $cs=Yii::app()->getClientScript();
    $cs->registerScript('unitDeleteDialog', <<<EOD

$(function() {
    // Обработчик нажатия кнопки "Удалить этот юнит только тут"
    $('#cms-pageunit-delete-this').click(function() {
        if (confirm('Вы действительно хотите удалить этот блок?'))
        {
            ajaxSave('/?r=page/unitDelete&pageunit_id[]={$pageunit_id}&unit_id={$unit_id}', '', 'GET', function(ret) {
                hideSplash();
                GetOutPageunitPanel();
                $('#cms-pageunit-{$pageunit_id}').remove();
            });
        }
        return false;
    });

    // Обработчик нажатия кнопки "Удалить этот юнит везде"
    $('#cms-pageunit-delete-all').click(function() {
        if (confirm('Вы действительно хотите удалить этот блок во всех местах? Удаляемая информация будет безвозвратно потеряна.'))
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

<h3>Удалить блок:</h3>
<ul>
    <li><a class="cms-button" id="cms-pageunit-delete-this" href="#">Из этой страницы</a></li>
    <li><a class="cms-button" id="cms-pageunit-delete-all" href="#">Со всех страниц</a></li>
    <li><a class="cms-button" id="cms-pageunit-delete-select" href="#">Выбрать</a></li>
</ul>