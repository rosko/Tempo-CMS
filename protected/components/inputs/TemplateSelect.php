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
        $data = call_user_func(array($className, 'getTemplates'), $className);
        if ($data != array()) {
            $data = array_merge(array(''=>Yii::t('cms', $this->empty)), $data);

            if($this->hasModel())
                echo CHtml::activeDropDownList($this->model,$this->attribute,$data, $this->htmlOptions);
            else
                echo CHtml::dropDownList($name,$this->value,$data,$this->htmlOptions);
        }
        if ($this->hasModel() && (get_class($this->model) == 'Unit'))
        {
            $unitId = $this->model->id;
            $injs = <<<JS
   cmsReloadPageUnit($('.cms-pageunit[rev={$unitId}]').attr('id').replace('cms-pageunit-',''), '.cms-pageunit[rev={$unitId}]');
JS;
        } else {
            $injs = '';
        }

        $escapedId = str_replace('.', '\\\\.', $id);
        $txtTemplateEditor = Yii::t('cms', 'Template editor');

        $js = 'js:'.<<<JS
cmsLoadDialog('/?r=filesEditor/form&type=templates&name={$className}&default='+$('#{$escapedId}').val(), {
        title: '{$txtTemplateEditor}',
        id: 'filesEditor',
        className: 'filesEditor',
        width: $(window).width()-200,
        height: 'auto',
        onSave: function(html) {
            {$injs}
            if ($('#filesEditor ul.files a.fileitem').length) {
                var html = '';
                var val = $('#{$escapedId}').val();
                $('#filesEditor ul.files a.fileitem').each(function() {
                    html += '<option value="'+$(this).attr('rev')+'">'+$(this).text()+'</option>';
                });
                var selected = $('#filesEditor ul.files a.fileitem.cms-hover');
                if (selected.length) {
                   val = selected.attr('rev');
                }
                if ($('#{$escapedId}').length) {
                    $('#{$escapedId}').html(html).val(val);
                } else {
                    $('<select id="{$id}" name="{$name}"></select>').insertAfter('label[for={$escapedId}]').html(html).val(val);
                }
            }
        },
        onClose: function() {
            {$injs}
            if ($('#filesEditor ul.files a.fileitem').length) {
                var html = '';
                var val = $('#{$escapedId}').val();
                $('#filesEditor ul.files a.fileitem').each(function() {
                    html += '<option value="'+$(this).attr('rev')+'">'+$(this).text()+'</option>';
                });
                var selected = $('#filesEditor ul.files a.fileitem.cms-hover');
                if (selected.length) {
                   val = selected.attr('rev');
                }
                if ($('#{$escapedId}').length) {
                    $('#{$escapedId}').html(html).val(val);
                } else {
                    $('<select id="{$id}" name="{$name}"></select>').insertAfter('label[for={$escapedId}]').html(html).val(val);
                }
            }
        }
    });
    return false;
JS;

        echo '<br /> ' . CHtml::link(Yii::t('cms', 'Edit templates'), '#', array('onclick' => $js));

    }


}
