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

        print "<ul><li>Ввести адрес ";
        if($this->hasModel()) 
                echo CHtml::activeTextField($this->model,$this->attribute,$this->htmlOptions);
        else
                echo CHtml::textField($name,$this->value,$this->htmlOptions);
        if ($this->showFileManagerButton)
        {
            print "</li><li>или ";
            echo CHtml::button('Выбрать файл из загруженных', array(
                'id' => $this->htmlOptions['id'] . '_button',
                'class' => 'cms-button',
            ));            
        }
        if ($this->showUploadButton)
        {
            print "</li><li>или <div id='".$this->htmlOptions['id']."_file'></div>";            
        }
        if ($this->showPageSelectButton)
        {
            print "</li><li>или Выбрать страницу<br />";
            $this->widget('PageSelect', array(
                'textLinkId' => $this->htmlOptions['id'],
                'name' => 'PageSelect',
                'id' => $this->htmlOptions['id'] . '_PageSelect'
            ));
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
            $js .= <<<EOD

$('#{$id}_button').click(function() {
	var url = '/3rdparty/fckeditor/editor/plugins/imglib/index.html#returnto={$id}';
	window.open( url, 'imglib','width=800, height=600, location=0, status=no, toolbar=no, menubar=no, scrollbars=yes, resizable=yes');
});
$('#{$id}').dblclick(function() {
    $('#{$id}_button').click();
});
EOD;
        }

        if ($this->showUploadButton)
        {
            $cs->registerScriptFile('/3rdparty/file-uploader/client/jquery.fileuploader.js');
            $js .= <<<EOD
var uploader = new qq.FileUploader({
    element: $('#{$id}_file')[0],
    action: '/3rdparty/file-uploader/server/php.php',
    allowedExtensions: {$extensions},
    template: '<div class="qq-uploader">' + 
                '<div class="cms-drop-area {$id}_drop"><span>{$id} Для загрузки переместите файл сюда</span></div>' +
                '<div class="cms-button w200">Загрузить с компьютера</div>' +
                '<ul class="qq-upload-list"></ul>' + 
             '</div>',
    fileTemplate: '<li>' +
            '<span class="qq-upload-file"></span>' +
            '<span class="cms-upload-spinner"></span>' +
            '<span class="qq-upload-size"></span>' +
            '<a class="qq-upload-cancel" href="#">Отмена</a>' +
            '<span class="qq-upload-failed-text">Ошибка</span>' +
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
        //serverError: "Some files were not uploaded, please contact support and/or try again.",
        typeError: "{file} имеет неподходящий тип. Допустимы файлы только следующих типов: {extensions}.",
        sizeError: "{file} слишком большой, максимальный допустимый размер файла {sizeLimit}.",
        emptyError: "{file} пуст, пожалуйста, выберите файлы снова без {file}."
    },
    showMessage: function(message) {
        showInfoPanel(message, 10);
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

        $cs->registerScript('Yii.Link#'.$id,$js);
        
    }
}

?>