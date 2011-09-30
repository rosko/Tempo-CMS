<?php

class PageSelect extends CInputWidget
{
    public $size = 35;
    public $textLinkId = null;
    public $multiple = false;
    public $excludeCurrent = true;
    public $width = null;
    public $height = 250;
    public $checked = array();
    public $checkedOnly = array();
    public $enabledOnly = null;
    public $canClear = true;
    
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
            
        if (isset($this->size))
            $this->htmlOptions['size'] = $this->size;
            
        $this->registerClientScript();

        if($this->hasModel()) 
            echo CHtml::activeHiddenField($this->model,$this->attribute,$this->htmlOptions);
        else
            echo CHtml::hiddenField($name,$this->value,$this->htmlOptions);

        $value = $this->hasModel() ? $this->model->{$this->attribute} : $this->value;
        $title = ($value) == 0 ? '' : Page::model()->findByPk($value)->title;
        $options = $this->htmlOptions;
        $options['id'] .= '_title';
        $options['readonly'] = true;
        unset($options['name']);
        
        echo '<div style="white-space:nowrap;">';
        if (!$this->multiple) {
            if ($this->hasModel() && $this->model->id == 1 && $this->attribute == 'parent_id')
            {
                echo Yii::t('cms', 'this is a home page');
            } else
                echo CHtml::textField('',$title,$options);
        }
        //echo $this->render('application.views.page.tree', array('tree' => Page::model()->getTree($this->model->id)), true);
        echo "</div><div style='border:1px solid gray;background:white;width:{$this->width}px;height:{$this->height}px;overflow:scroll;" . (!$this->multiple ? "position:absolute;display:none;" : "") . "' id='" . $this->htmlOptions['id'] . "_dialog'></div>";

    }    

    public function registerClientScript()
    {
        $id=$this->htmlOptions['id'];
        $value = $this->hasModel() ? $this->model->{$this->attribute} : $this->value;
        
        $textLinkJs = '';
        if ($this->textLinkId)
        {
            $textLinkJs = <<<JS
$.ajax({
    url: '/?r=page/getUrl&pageId='+id+'&language='+$.data(document.body, 'language'),
    cache: false,
    beforeSend: function() {
        cmsShowInfoPanel(cmsHtmlLoadingImage, 0);
    },
    success: function(html) {
        cmsHideInfoPanel();
        $('#{$this->textLinkId}').val(html);
    }
});
JS;
        }

        $modelId = $this->hasModel()&&$this->excludeCurrent ? $this->model->id : 0;
        $ajax_data = "'";
        if ($this->enabledOnly != null)
        {
            $ajax_data .= "'+$.param({enabledOnly: " . CJavaScript::jsonEncode($this->enabledOnly) . "})+'";
        }
        $ajax_data .= "'";

        if (!$this->multiple) {

            if ($this->canClear) {
                $txtClear = Yii::t('cms', 'Clear');
                $js = <<<JS
$( "<a>&nbsp;</a>" )
    .attr( "tabIndex", -1 )
    .attr( "title", "{$txtClear}" )
    .insertAfter('#{$id}_title')
    .button({
        icons: {
            primary: "ui-icon-closethick"
        },
        text: false
    })
    .removeClass( "ui-corner-all" )
    .addClass( "ui-corner-right ui-button-icon" )
    .click(function() {
        $('#{$id}').val(0);
        $('#{$id}_title').val('');
        return false;
    });
    
JS;
            }
            $txtShowlist = Yii::t('cms', 'Show list');
            $txtLoading = Yii::t('cms', 'loading');
            $js .= <<<JS
$( "<a>&nbsp;</a>" )
    .attr( "tabIndex", -1 )
    .attr( "title", "{$txtShowlist}" )
    .insertAfter('#{$id}_title')
    .button({
        icons: {
            primary: "ui-icon-triangle-1-s"
        },
        text: false
    })
    .removeClass( "ui-corner-all" )
    .addClass( "ui-button-icon" )
    .click(function() {
        $('#{$id}_title').click();
        return false;
    });


$('#{$id}_title').click(function() {
    if ($('#{$id}_dialog').html() == '') {
        $('#{$id}_dialog').html('{$txtLoading}...');
        $.ajax({
            url: '/?r=page/tree&pageId={$modelId}&treeId=pagetree_{$id}&multiple=0&language='+$.data(document.body, 'language'),
            data: {$ajax_data},
            method: 'POST',
            cache: false,
            success: function(html) {
                $('#{$id}_dialog').html(html).show().css('width', $('#{$id}_title').width());
                //$('#{$id}_dialog').dialog({ zIndex: 100000});            
    
                $('#pagetree_{$id}').bind('dblclick.jstree', function(e) {
                    if ($(e.target).attr('rev') == 'page') {
                        var id = $(e.target).attr('rel');
                        var title = $(e.target).text();
                        $('#{$id}_dialog').hide().html('');
                        $('#{$id}').val(id);
                        $('#{$id}_title').val(title);
                        {$textLinkJs}
                        e.stopImmediatePropagation();
                        return false;
                    }
                });
                $(document).bind('keydown', 'esc', function(e) {
                   $('#{$id}_dialog').hide().html('');
                }).bind('click', function(e) {
                   if (!$(e.target).parents('#{$id}_dialog').length && e.target.id != '{$id}_dialog') {
                       $('#{$id}_dialog').hide().html('');
                   }
                });
                setTimeout(function() {
                  //$('#pagetree_{$id}').jstree('select_node', '#pagetree_{$id}-{$value}');
                  $('#pagetree_{$id}-{$value}').children("a:eq(0)").click();
                  $('#{$id}_dialog').click();
                }, 100);
                $('#{$id}_dialog').blur(function(e){
                    $('#{$id}_dialog').hide().html('');
                });
                
    
            }
        });
    }
}).focusin(function() {
    $(this).click();
});

JS;
        }
        else
        {
            $checked = '';
            if (!empty($this->checked)) {
                $checked = "#pagetree_{$id}-".implode(", #pagetree_{$id}-", $this->checked);
            }
            if (!empty($this->checkedOnly)) {
                $checkedOnly = "#pagetree_{$id}-".implode(", #pagetree_{$id}-", $this->checkedOnly);
            }

            $js = <<<JS

$(function() {
    $.ajax({
        url: '/?r=page/tree&pageId={$modelId}&treeId=pagetree_{$id}&multiple=1&language='+$.data(document.body, 'language'),
        cache: false,
        data: {$ajax_data},
        method: 'POST',
        success: function(html) {
            $('#{$id}_dialog').html(html).show();
            //$('#{$id}_dialog').dialog({ zIndex: 100000});            

            setTimeout(function() {
                //$('#pagetree_{$id}').jstree('select_node', '#pagetree_{$id}-1');
                //$('#pagetree_{$id}-1').children("a:eq(0)").click();
                //$('#pagetree_{$id}').jstree('uncheck_all');
                $('#{$id}_dialog').click();
                if ('{$checked}' != '') {
                    $('#pagetree_{$id}').jstree('open_all', $('{$checked}'));
                    $('#pagetree_{$id}').jstree('check_node_all', $('{$checked}'));
                } else if ('{$checkedOnly}' != '') {
                     $('#pagetree_{$id}').jstree('open_all', $('{$checkedOnly}'));
                     $('#pagetree_{$id}').jstree('check_node', $('{$checkedOnly}'));
                }
            }, 100);
        }
    });
});

JS;
        }

        if ($this->hasModel() && $this->model->id == 1 && $this->attribute == 'parent_id')
        {
            $js = <<<JS
$('.field_parent_id').hide();
JS;
        }

        $cs=Yii::app()->getClientScript();
        $cs->registerScript('Yii.PageSelect#'.$id,$js);
        $cs->registerPackage('jstree');

    }
}
