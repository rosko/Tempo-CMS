<?php

class AreaEdit extends CInputWidget
{
    
    public function run()
    {
        list($name,$id)=$this->resolveNameID();
        if(isset($this->htmlOptions['id']))
            $id=$this->htmlOptions['id'];
        else
            $this->htmlOptions['id']=$id;
        if(isset($this->htmlOptions['name']))
            $name=$this->htmlOptions['name'];
        else
            $this->htmlOptions['name']=$name;
            
        if($this->hasModel()) {
            $areaName = 'widget'.$this->model->widget->id.$id;
            $modelId = $this->model->id;
            if ($this->model->isNewRecord) {
                echo Yii::t('cms', 'Please save before using it.');
                return;
            }
            echo CHtml::activeHiddenField($this->model,$this->attribute,$this->htmlOptions);
        } else {
            $areaName = $name;
            $modelId = $id;
            echo CHtml::hiddenField($name,$this->value,$this->htmlOptions);
        }

        $value = $this->hasModel() ? $this->model->{$this->attribute} : $this->value;
        Yii::app()->controller->widget('Area', array(
            'name'=>$areaName,
            'pageWidgets'=> PageWidget::model()->findAll(array(
                'condition' => '`area` = :area',
                'params' => array(
                    'area' => $areaName,
                ),
                'with' => array('widget'),
                'order' => '`order`'
            ))
        ));
?>
<script type="text/javascript">

            // Настройки и обработчики перещения юнитов на странице
            $('#cms-area-<?=$areaName?>').sortable({
                placeholder: 'cms-pagewidget-highlight',
                revert: true,
                opacity:1,
                forcePlaceholderSize:true,
                cancel:'.cms-pagewidget-menu,.cms-empty-area-buttons',
                update:function(event, ui) {
                    var pageWidgetId = $(ui.item).attr('id').replace('cms-pagewidget-','');
                    var areaName = cmsGetAreaNameByPageWidget(ui.item);
                    if (!ui.sender) {
                        // Запрос на обновление текущей области
                        cmsAjaxSaveArea(cmsGetAreaByPageWidget(ui.item), areaName, <?=intval($modelId)?>, 'pageWidgetId='+pageWidgetId);
                    }
                },
                start:function(event, ui) {
                    $(ui.helper).find('.cms-panel').hide();
                    $('.cms-area').addClass('cms-potential');
                    $('.cms-area').each(function() {
                        if ($(this).find('.cms-pagewidget').length == 0)
                            $(this).addClass('cms-empty-area');
                    });
                    cmsAreaEmptyCheck();
                },
                stop:function(event, ui) {
                    $('.cms-area').removeClass('cms-potential').removeClass('cms-empty-area');
                    cmsAreaEmptyCheck();
                }
            }).disableSelection();

            $('#cms-area-<?=$areaName?> .cms-pagewidget').css('cursor', 'move');

            cmsAreaEmptyCheck();


</script>
<style type="text/css">
    #cms-area-<?=$areaName?> #pagewidgetpanel_move_li {
        display:none;
    }
</style>
<?php    
    }
    
}