<?php

class TemplateSelect extends CInputWidget
{
    public $className;
    public $empty = '«обычный»';

    public function run()
	{
        list($name,$id)=$this->resolveNameID();

		if(isset($this->htmlOptions['id']))
			$id=$this->htmlOptions['id'];
		else
			$this->htmlOptions['id']=$id;

		if(isset($this->htmlOptions['name']))
			$name=$this->htmlOptions['name'];

        $className = $this->className;

        $value = $this->hasModel() ? $this->model->{$this->attribute} : $this->value;
        $data = $className::getTemplates($className);
        if ($data != array()) {
            $data = array_merge(array(''=>$this->empty), $data);

            if($this->hasModel())
                echo CHtml::activeDropDownList($this->model,$this->attribute,$data, $this->htmlOptions);
            else
                echo CHtml::dropDownList($name,$this->value,$data,$this->htmlOptions);
        }
        if ($this->hasModel())
        {
            $unit_id = $this->model->id;
            $injs = <<<EOD
   updatePageunit($('.cms-pageunit[rev={$unit_id}]').attr('id').replace('cms-pageunit-',''), '.cms-pageunit[rev={$unit_id}]');
EOD;
        }

        echo '<br /> ' . CHtml::link('Редактировать шаблоны', '#', array('id'=>$id.'_editor'));

        $js = <<<EOD

$('#{$id}_editor').click(function() {

    loadDialog({
        url: '/?r=filesEditor/form&type=templates&name={$className}&default='+$('#{$id}').val(),
        title: 'Редактор шаблонов',
        id: 'filesEditor',
        className: 'filesEditor',
        width: $(window).width()-200,
        height: 'auto',
        onSubmit: function() {
            return false;
        },
        onSave: function(html) {
            {$injs}
            if ($('#filesEditor ul.files a.fileitem').length) {
                var html = '';
                var val = $('#{$id}').val();
                $('#filesEditor ul.files a.fileitem').each(function() {
                    html += '<option value="'+$(this).attr('rev')+'">'+$(this).text()+'</option>';
                });
                var selected = $('#filesEditor ul.files a.fileitem.hover');
                if (selected) {
                   val = selected.attr('rev');
                }
                if ($('#{$id}').length) {
                    $('#{$id}').html(html).val(val);
                } else {
                    $('<select id="{$id}" name="{$name}"></select>').insertAfter('label[for={$id}]').html(html).val(val);
                }
            }
        },
        onClose: function() {
            if ($('#filesEditor ul.files a.fileitem').length) {
                var html = '';
                var val = $('#{$id}').val();
                $('#filesEditor ul.files a.fileitem').each(function() {
                    html += '<option value="'+$(this).attr('rev')+'">'+$(this).text()+'</option>';
                });
                var selected = $('#filesEditor ul.files a.fileitem.hover');
                if (selected) {
                   val = selected.attr('rev');
                }
                if ($('#{$id}').length) {
                    $('#{$id}').html(html).val(val);
                } else {
                    $('<select id="{$id}" name="{$name}"></select>').insertAfter('label[for={$id}]').html(html).val(val);
                }
            }
        }
    });
    return false;
});
EOD
;
        $cs = Yii::app()->getClientScript();
        $cs->registerScript(__CLASS__.'#'.$id, $js);
    }


}
