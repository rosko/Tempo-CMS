<?php

class TemplateSelect extends CInputWidget
{
    public $className;
    public $templateType;
    public $empty = '«default»';
    public $hideDefault = true;

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

        if (is_subclass_of($this->className, 'ContentWidget')) {
            $unitClassName = call_user_func(array($this->className, 'unitClassName'));
            $widgetClassName = $this->className;
        } else {
            $unitClassName = $this->className;
            $widgetClassName = null;
        }

        if ($this->templateType) {

            $this->showTemplateSelectForType($this->templateType);

        } else {

            if ($widgetClassName) {
                $this->showTemplateSelectForWidget($widgetClassName);
            } else {
                $widgets = call_user_func(array($unitClassName, 'widgets'));
                foreach ($widgets as $widgetClassName) {
                    $this->showTemplateSelectForWidget($widgetClassName);
                }
            }
        }

        if ($this->hasModel() && (get_class($this->model) == 'Widget'))
        {
            $widgetId = $this->model->id;
            $injs = <<<JS
   cmsReloadPageWidget($('.cms-pagewidget[rev={$widgetId}]').attr('id').replace('cms-pagewidget-',''), '.cms-pagewidget[rev={$widgetId}]');
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

    protected function showTemplateSelectForType($templateType, $htmlOptions='')
    {
        if (is_subclass_of($this->className, 'ContentWidget')) {
            $className = call_user_func(array($this->className, 'unitClassName'));
        } else {
            $className = $this->className;
        }
        $data = array_keys(ContentUnit::getTemplates($className, $templateType));
        $data = array_combine($data, $data);
        if ($this->hideDefault)
            unset($data['default']);
        $value = $this->hasModel() ? $this->model->{$this->attribute} : $this->value;
        $array_value = @unserialize($value);
        if ($array_value !== false && isset($array_value[$templateType])) {
            $value = $array_value[$templateType];
        }

        if (is_array($data)) {
            $data = array_merge(array(''=>Yii::t('cms', $this->empty)), $data);
            if (!$htmlOptions) {
                $htmlOptions = $this->htmlOptions;
            }
            echo CHtml::dropDownList($htmlOptions['name'],$value,$data,$htmlOptions);
        }

    }

    protected function showTemplateSelectForWidget($widgetClassName)
    {
        $templates = call_user_func(array($widgetClassName, 'templates'), $widgetClassName);
        foreach ($templates as $templateType => $templateTypeTitle) {

            $htmlOptions = $this->htmlOptions;
            $htmlOptions['id'] .= '_'.$templateType;
            $htmlOptions['name'] .= '['.$templateType.']';
            echo CHtml::label($templateTypeTitle,$htmlOptions['id'],$this->htmlOptions);
            $this->showTemplateSelectForType($templateType, $htmlOptions);
        }

    }


}
