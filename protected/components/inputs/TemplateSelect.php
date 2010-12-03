<?php

class TemplateSelect extends CInputWidget
{
    public $className;
    public $empty = '«default»';

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

        $className = $this->className;

        $value = $this->hasModel() ? $this->model->{$this->attribute} : $this->value;
        $data = $className::getTemplates($className);
        if ($data != array()) {
            $data = array_merge(array(''=>Yii::t('cms', $this->empty)), $data);

            if($this->hasModel())
                echo CHtml::activeDropDownList($this->model,$this->attribute,$data, $this->htmlOptions);
            else
                echo CHtml::dropDownList($name,$this->value,$data,$this->htmlOptions);
        }
        if ($this->hasModel() && (get_class($this->model) == 'Unit'))
        {
            $unit_id = $this->model->id;
            $injs = <<<EOD
   updatePageunit($('.cms-pageunit[rev={$unit_id}]').attr('id').replace('cms-pageunit-',''), '.cms-pageunit[rev={$unit_id}]');
EOD;
        } else {
            $injs = '';
        }

        $escaped_id = str_replace('.', '\\\\.', $id);
        $txtTemplateEditor = Yii::t('cms', 'Template editor');

        $js = <<<EOD
js:loadDialog({
        url: '/?r=filesEditor/form&type=templates&name={$className}&default='+$('#{$escaped_id}').val(),
        title: '{$txtTemplateEditor}',
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
                var val = $('#{$escaped_id}').val();
                $('#filesEditor ul.files a.fileitem').each(function() {
                    html += '<option value="'+$(this).attr('rev')+'">'+$(this).text()+'</option>';
                });
                var selected = $('#filesEditor ul.files a.fileitem.hover');
                if (selected.length) {
                   val = selected.attr('rev');
                }
                if ($('#{$escaped_id}').length) {
                    $('#{$escaped_id}').html(html).val(val);
                } else {
                    $('<select id="{$id}" name="{$name}"></select>').insertAfter('label[for={$escaped_id}]').html(html).val(val);
                }
            }
        },
        onClose: function() {
            {$injs}
            if ($('#filesEditor ul.files a.fileitem').length) {
                var html = '';
                var val = $('#{$escaped_id}').val();
                $('#filesEditor ul.files a.fileitem').each(function() {
                    html += '<option value="'+$(this).attr('rev')+'">'+$(this).text()+'</option>';
                });
                var selected = $('#filesEditor ul.files a.fileitem.hover');
                if (selected.length) {
                   val = selected.attr('rev');
                }
                if ($('#{$escaped_id}').length) {
                    $('#{$escaped_id}').html(html).val(val);
                } else {
                    $('<select id="{$id}" name="{$name}"></select>').insertAfter('label[for={$escaped_id}]').html(html).val(val);
                }
            }
        }
    });
    return false;
EOD
;

        echo '<br /> ' . CHtml::link(Yii::t('cms', 'Edit templates'), '#', array('onclick' => $js));

    }


}
