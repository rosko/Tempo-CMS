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
            $areaName = 'unit'.$this->model->unit->id.$id;
            $modelId = $this->model->id;
            echo CHtml::activeHiddenField($this->model,$this->attribute,$this->htmlOptions);
        } else {
            $areaName = $name;
            $modelId = $id;
            echo CHtml::hiddenField($name,$this->value,$this->htmlOptions);
        }

        $value = $this->hasModel() ? $this->model->{$this->attribute} : $this->value;
        Yii::app()->controller->widget('Area', array(
            'name'=>$areaName,
            'pageUnits'=> PageUnit::model()->findAll(array(
                'condition' => '`area` = :area',
                'params' => array(
                    'area' => $areaName,
                ),
                'with' => array('unit'),
                'order' => '`order`'
            ))
        ));
?>
<script type="text/javascript">

            // Настройки и обработчики перещения юнитов на странице
            $('#cms-area-<?=$areaName?>').sortable({
                placeholder: 'cms-pageunit-highlight',
                revert: true,
                opacity:1,
                forcePlaceholderSize:true,
                cancel:'.cms-pageunit-menu,.cms-empty-area-buttons',
                update:function(event, ui) {
                    var pageUnitId = $(ui.item).attr('id').replace('cms-pageunit-','');
                    var areaName = cmsGetAreaNameByPageUnit(ui.item);
                    if (!ui.sender) {
                        // Запрос на обновление текущей области
                        cmsAjaxSaveArea(cmsGetAreaByPageUnit(ui.item), areaName, <?=$modelId?>, 'pageUnitId='+pageUnitId);
                    }
                },
                start:function(event, ui) {
                    $(ui.helper).find('.cms-panel').hide();
                    $('.cms-area').addClass('cms-potential');
                    $('.cms-area').each(function() {
                        if ($(this).find('.cms-pageunit').length == 0)
                            $(this).addClass('cms-empty-area');
                    });
                    cmsAreaEmptyCheck();
                },
                stop:function(event, ui) {
                    $('.cms-area').removeClass('cms-potential').removeClass('cms-empty-area');
                    cmsAreaEmptyCheck();
                }
            }).disableSelection();

            $('#cms-area-<?=$areaName?> .cms-pageunit').css('cursor', 'move');

            cmsAreaEmptyCheck();


</script>
<style type="text/css">
    #cms-area-<?=$areaName?> #pageunitpanel_move_li {
        display:none;
    }
</style>
<?php    
    }
    
}