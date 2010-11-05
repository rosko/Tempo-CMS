<?php

class PageSelect extends CInputWidget
{
    public $size = 40;
    public $textLinkId = null;
    public $multiple = false;
    public $excludeCurrent = true;
    public $width = null;
    public $height = 250;
    public $checked = array();
    public $checkedOnly = array();
    public $enabledOnly = null;
    
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
        
        if (!$this->multiple) {
            if ($this->hasModel() && $this->model->id == 1 && $this->attribute == 'parent_id')
            {
                echo 'это главная страница сайта';    
            } else
                echo CHtml::textField('',$title,$options);
        }
        //echo $this->render('application.views.page.pagetree', array('tree' => Page::model()->getTree($this->model->id)), true);
        echo "<div style='border:1px solid gray;background:white;width:{$this->width}px;height:{$this->height}px;overflow:scroll;" . (!$this->multiple ? "position:absolute;display:none;" : "") . "' id='" . $this->htmlOptions['id'] . "_dialog'></div>";

    }    

    public function registerClientScript()
    {
        $id=$this->htmlOptions['id'];
        
        $textLinkJs = '';
        if ($this->textLinkId)
        {
            $textLinkJs = <<<EOD
$.ajax({
    url: '/?r=page/getUrl&id='+id,
    cache: false,
    beforeSend: function() {
        showInfoPanel(cms_html_loading_image, 0);
    },
    success: function(html) {
        hideInfoPanel();
        $('#{$this->textLinkId}').val(html);
    }
});
EOD;
        }

        $model_id = $this->hasModel()&&$this->excludeCurrent ? $this->model->id : 0;
        $ajax_data = "'";
        if ($this->enabledOnly != null)
        {
            $ajax_data .= "'+$.param({enabledOnly: " . CJavaScript::jsonEncode($this->enabledOnly) . "})+'";
        }
        $ajax_data .= "'";

        if (!$this->multiple) {
            $js = <<<EOD

$('#{$id}_title').click(function() {
    if ($('#{$id}_dialog').html() == '') {
        $('#{$id}_dialog').html('loading...');
        $.ajax({
            url: '/?r=page/pageTree&id={$model_id}&tree_id=pagetree_{$id}&multiple=0',
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
                }).bind('keydown', 'esc', function(e) {
                   $('#{$id}_dialog').hide().html('');
                });
                setTimeout(function() {
                  //$('#pagetree_{$id}').jstree('select_node', '#pagetree_{$id}-1');
                  $('#pagetree_{$id}-1').children("a:eq(0)").click();
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

EOD;
        }
        else
        {
            $checked = "#pagetree_{$id}-".implode(", #pagetree_{$id}-", $this->checked);
            if (!empty($this->checkedOnly)) {
                $checked = '';
                $checkedOnly = "#pagetree_{$id}-".implode(", #pagetree_{$id}-", $this->checkedOnly);
            }

            $js = <<<EOD

$(function() {
    $.ajax({
        url: '/?r=page/pageTree&id={$model_id}&tree_id=pagetree_{$id}&multiple=1',
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
                    $('#pagetree_{$id}').jstree('check_node_all', $('{$checked}'));
                    $('#pagetree_{$id}').jstree('open_all', $('{$checked}'));
                } else if ('{$checkedOnly}' != '') {
                     $('#pagetree_{$id}').jstree('check_node', $('{$checkedOnly}'));
                     $('#pagetree_{$id}').jstree('open_all', $('{$checkedOnly}'));
                }
            }, 100);
        }
    });
});

EOD;
        }

        if ($this->hasModel() && $this->model->id == 1 && $this->attribute == 'parent_id')
        {
            $js = <<<EOD
$('.field_parent_id').hide();
EOD;
        }

        $cs=Yii::app()->getClientScript();
        $cs->setCoreScriptUrl('/js/empty');
        $cs->registerScript('Yii.PageSelect#'.$id,$js);

    }
}
