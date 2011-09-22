<?php

class FileInput extends CInputWidget
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
            print "</li><li>" . Yii::t('cms', 'or') . " ";
            echo CHtml::button(Yii::t('cms', 'Browse uploaded'), array(
                'id' => $this->htmlOptions['id'] . '_button',
                'class' => 'cms-button w200',
            ));            
        }
        if ($this->showUploadButton)
        {
            print "</li><li" . Yii::t('cms', 'or') . " <div id='".$this->htmlOptions['id']."_file'></div>";
        }
        print "</li></ul>";
        
        
    }
    
    public function registerClientScript()
    {
        $id=$this->htmlOptions['id'];
        $extensions = CJavaScript::jsonEncode($this->extensions);
        $cs=Yii::app()->getClientScript();
        $js = '';
        if ($this->showFileManagerButton)
        {
            $am=Yii::app()->getAssetManager();
            $fckeditorPath= $am->publish(Yii::getPathOfAlias('application.vendors.fckeditor'));
            $js .= <<<EOD

$('#{$id}_button').click(function() {
	var url = '{$fckeditorPath}/editor/plugins/imglib/index.html#returnto={$id}';
	window.open( url, 'imglib','width=800, height=600, location=0, status=no, toolbar=no, menubar=no, scrollbars=yes, resizable=yes');
});
$('#{$id}').dblclick(function() {
    $('#{$id}_button').click();
});
EOD;
        }

        if ($this->showUploadButton)
        {
            $fileuploaderPath=$am->publish(Yii::getPathOfAlias('application.vendors.file-uploader'));
            $cs->registerCssFile($fileuploaderPath.'/client/fileuploader.css');
            $cs->registerScriptFile($fileuploaderPath.'/client/jquery.fileuploader.js');
            $txtDragHere = Yii::t('cms', 'Drag here');
            $txtUpload = Yii::t('cms', 'Upload');
            $txtServerError = Yii::t('cms', 'Some files were not uploaded, please contact support and/or try again.');
            $txtTypeError = Yii::t('cms', '{file} has wrong type. Allowed only next types: {extensions}.');
            $txtSizeError = Yii::t('cms', '{file} too big, maximum allowed size is {sizeLimit}.');
            $txtEmptyError = Yii::t('cms' , '{file} is empty, please, choose files again except {file}.');
            $js .= <<<EOD
var uploader = new qq.FileUploader({
    element: document.getElementById('{$id}_file'),
    action: '{$fileuploaderPath}/server/php.php',
    allowedExtensions: {$extensions},
    template: '<div class="qq-uploader">' + 
                '<div class="cms-drop-area"><span>{$txtDragHere}</span></div>' +
                '<div class="cms-button w200">{$txtUpload}</div>' +
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
        drop: 'cms-drop-area',
        dropActive: 'cms-drop-area-active',
        list: 'qq-upload-list',

        file: 'qq-upload-file',
        spinner: 'qq-upload-spinner',
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
EOD;
            
        }

        if ($this->onChange !== null) {
            if (substr($this->onChange,0,3) == 'js:') {
                $this->onChange = substr($this->onChange,3);
            }
            $js .= <<<EOD
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
EOD;
        }

        $cs->registerScript('Yii.FileInput#'.$id,$js);
        
    }
}
