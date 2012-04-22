<?php

class Link extends CInputWidget
{
    public $size = null;
    public $onChange = null;
    public $extensions = array();
    public $showFileManagerButton = true;
    public $showUploadButton = true;
    public $showPageSelectButton = true;
    
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
            
        if ($this->size !== null) {
            $this->htmlOptions['size'] = $this->size;
        }

        $this->registerClientScript();

        print "<ul><li>" . Yii::t('cms', 'URL') . " ";
        if($this->hasModel()) 
                echo CHtml::activeTextField($this->model,$this->attribute,$this->htmlOptions);
        else
                echo CHtml::textField($name,$this->value,$this->htmlOptions);
        if ($this->showFileManagerButton)
        {
            echo "</li><li>" . Yii::t('cms', 'or') . " ";
            echo CHtml::button(Yii::t('cms', 'Browse uploaded'), array(
                'id' => $this->htmlOptions['id'] . '_button',
                'class' => 'cms-button',
            ));            
        }
        if ($this->showUploadButton)
        {
            echo "</li><li>" . Yii::t('cms', 'or') . " <div id='".$this->htmlOptions['id']."_file'></div>";
        }
        if ($this->showPageSelectButton)
        {
            echo "</li><li>" . Yii::t('cms', 'or') . " " . Yii::t('cms', 'Select page') . "<br />";
            $this->widget('PageSelect', array(
                'textLinkId' => $this->htmlOptions['id'],
                'name' => 'PageSelect',
                'id' => $this->htmlOptions['id'] . '_PageSelect'
            ));
        }
        echo "</li></ul>";
        
        
    }
    
    public function registerClientScript()
    {
        $id=$this->htmlOptions['id'];
        $extensions = CJavaScript::jsonEncode($this->extensions);
        $cs=Yii::app()->getClientScript();
        $am=Yii::app()->getAssetManager();
        $js = '';
        if ($this->showFileManagerButton)
        {
            $_ext = implode(',', $this->extensions);
            $js .= <<<JS

$('#{$id}_button').click(function() {
	var url = '/?r=files/manager&extensions={$_ext}&returnto={$id}';
	window.open( url, 'imglib','width=1050, height=550, location=0, status=no, toolbar=no, menubar=no, scrollbars=yes, resizable=yes');
});
$('#{$id}').dblclick(function() {
    $('#{$id}_button').click();
});
JS;
        }

        if ($this->showUploadButton)
        {
            /*
            $fileuploaderPath = $am->publish(Yii::getPathOfAlias('application.vendors.file-uploader'));
            $cs->registerCssFile($fileuploaderPath.'/client/fileuploader.css');
            $cs->registerScriptFile($fileuploaderPath.'/client/jquery.fileuploader.js');
            $txtDragHere = Yii::t('cms', 'Drag here');
            $txtUpload = Yii::t('cms', 'Upload');
            $txtCancel = Yii::t('cms', 'Cancel');
            $txtError = Yii::t('cms', 'Error');
            $txtServerError = Yii::t('cms', 'Some files were not uploaded, please contact support and/or try again.');
            $txtTypeError = Yii::t('cms', '{file} has wrong type. Allowed only next types: {extensions}.');
            $txtSizeError = Yii::t('cms', '{file} too big, maximum allowed size is {sizeLimit}.');
            $txtEmptyError = Yii::t('cms' , '{file} is empty, please, choose files again except {file}.');
            $js .= <<<JS
var uploader = new qq.FileUploader({
    element: $('#{$id}_file')[0],
    action: '/?r=files/manager&cmd=upload&datedir=1',
    allowedExtensions: {$extensions},
    template: '<div class="qq-uploader">' + 
                '<div class="cms-drop-area {$id}_drop"><span>{$txtDragHere}</span></div>' +
                '<div class="cms-button cms-w200">{$txtUpload}</div>' +
                '<ul class="qq-upload-list"></ul>' + 
             '</div>',
    fileTemplate: '<li>' +
            '<span class="qq-upload-file"></span>' +
            '<span class="cms-upload-spinner"></span>' +
            '<span class="qq-upload-size"></span>' +
            '<a class="qq-upload-cancel" href="#">{$txtCancel}</a>' +
            '<span class="qq-upload-failed-text">{$txtError}</span>' +
        '</li>',
    onComplete: function(id, fileName, ret){
        if (ret.success)
        {
            $('.qq-upload-list').html('');
            $('#{$id}').val('http://'+location.hostname+'/files/'+ret.filename).change();
        }
    },
    classes: {
        button: 'cms-button',
        drop: '{$id}_drop',
        dropActive: 'cms-drop-area-active',
        list: 'qq-upload-list',

        file: 'qq-upload-file',
        spinner: 'cms-upload-spinner',
        size: 'qq-upload-size',
        cancel: 'qq-upload-cancel',

        success: 'qq-upload-success',
        fail: 'qq-upload-fail'
    },
    messages: {
        //serverError: "{$txtServerError}",
        typeError: "{$txtTypeError}",
        sizeError: "{$txtSizeError}",
        emptyError: "{$txtEmptyError}"
    },
    showMessage: function(message) {
        cmsShowInfoPanel(message, 10);
    }
});    
JS;
*/
            $elfinderPath = $am->publish(Yii::getPathOfAlias('application.vendors.elfinder2'));

            $cs->registerCssFile($elfinderPath.'/css/elfinder.min.css', 'screen');
            $cs->registerScriptFile($elfinderPath.'/js/elfinder.min.js');
            $cs->registerCssFile($elfinderPath.'/css/theme.css', 'screen');

            $csrfTokenName = Yii::app()->getRequest()->csrfTokenName;
            $csrfToken = Yii::app()->getRequest()->getCsrfToken();

        }

        if ($this->onChange !== null) {
            if (substr($this->onChange,0,3) == 'js:') {
                $this->onChange = substr($this->onChange,3);
            }
            $js .= <<<JS
$('#{$id}').change(function() {
    {$this->onChange}
});
$('#{$id}').select(function() {
    {$this->onChange}
});
$('#{$id}').mousemove(function() {
    {$this->onChange}
});
$('#{$id}').focusout(function() {
    {$this->onChange}
});
JS;
        }

        $cs->registerScript('Yii.Link#'.$id,$js);
        
    }
}
