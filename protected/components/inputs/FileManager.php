<?php
// - В списке выбранных файлов/картинок, когда подводишь к эскизу, что всплывает увеличенное изображение
// - Сделать глобальную настройку, чтобы внешние файлы автоматически подкачивались в хранилище сайта.
// И чтобы ссылка ставилась на уже скачанный файл

class FileManager extends CInputWidget
{
    public $multiple = false;
    public $connectorUrl = '/?r=files/managerConnector';
    public $selectFiles = true;
    public $showFileManagerByDefault = 'auto';
    public $width = 'auto';
    public $height = 500;
    public $cssClassName = 'cms-filemanager';
    public $options = array();
    public $element = array();

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

        // Default options
        $this->options = CMap::mergeArray(array(
                'commandsOptions' => array(
                    'getfile' => array(
                        'onlyURL' => false,
                    ),
                ),
        ), $this->options);

        if ($this->multiple && $this->selectFiles) {
            $this->options['commandsOptions']['getfile']['multiple'] = true;
        }


/*
        if($this->hasModel())
            echo CHtml::activeHiddenField($this->model,$this->attribute,$this->htmlOptions);
        else
            echo CHtml::hiddenField($name,$this->value,$this->htmlOptions);
*/
        $value = $this->hasModel() ? $this->model->{$this->attribute} : $this->value;
        if (!is_array($value)) $value = @unserialize($value);
        if (strtolower($this->showFileManagerByDefault) == 'auto') {
            $this->showFileManagerByDefault = empty($value);
        }

        $this->registerClientScript();
        $this->render('FileManager', array(
                'id' => $id,
                'name' => $name,
                'selectFiles' => $this->selectFiles,
                'value' => $value,
                'height' => $this->height,
                'multiple' => $this->multiple,
                'element' => $this->element,
                'showFileManagerByDefault' => $this->showFileManagerByDefault,
                'cssClassName' => $this->cssClassName,
        ));
    }

    protected function registerClientScript()
    {
        $cs = Yii::app()->getClientScript();
        $am = Yii::app()->getAssetManager();
        $id = $this->htmlOptions['id'];
        $name = $this->htmlOptions['name'];

        $language = Yii::app()->language;
        $csrfTokenName = Yii::app()->getRequest()->csrfTokenName;
        $csrfToken = Yii::app()->getRequest()->getCsrfToken();

        $elfinderPath = $am->publish(Yii::getPathOfAlias('application.vendors.elfinder2'));

        if (!Yii::app()->request->isAjaxRequest) {
            $cs->registerPackage('jquery.uicss');
            $cs->registerPackage('cmsDialogs');
            $cs->registerScript('all', <<<JS

        $.data(document.body, 'language', '{$language}');
        $.data(document.body, 'csrfTokenName', '{$csrfTokenName}');
        $.data(document.body, 'csrfToken', '{$csrfToken}');

JS
);
        }

        $cs->registerCssFile($elfinderPath.'/css/elfinder.min.css', 'screen');
        $cs->registerScriptFile($elfinderPath.'/js/elfinder.min.js');
        $cs->registerCssFile($elfinderPath.'/css/theme.css', 'screen');

        $this->options['url'] = $this->connectorUrl;
        $this->options['customData'][Yii::app()->getRequest()->csrfTokenName]
            = Yii::app()->getRequest()->getCsrfToken();

        // Подключаем язык интерфейса
        if (is_file(Yii::getPathOfAlias('application.vendors.elfinder2.js.i18n') . DIRECTORY_SEPARATOR . 'elfinder.' . Yii::app()->language . '.js')) {
            $cs->registerScriptFile($elfinderPath.'/js/i18n/elfinder.'.Yii::app()->language.'.js');
            $this->options['lang'] = Yii::app()->language;
        }

        // Интеграция с CKEditor
        if (Yii::app()->request->getParam('CKEditor')) {
            $funcNum = Yii::app()->request->getQuery('CKEditorFuncNum');
            $this->options['getFileCallback'] = 'js:'.<<<DATA
function(file) {
    window.opener.CKEDITOR.tools.callFunction('{$funcNum}', file.url);
    window.close();
}
DATA;
        }

        // Интеграция с другими "открывателями" файлменеджера
        if (Yii::app()->request->getParam('returnto')) {
            $returnto = Yii::app()->request->getParam('returnto');
            $this->options['getFileCallback'] = 'js:'.<<<DATA
function(file) {
    window.opener.document.getElementById('{$returnto}').value = file.url;
    window.close();
}
DATA;
        }

        if ($this->selectFiles) {
            $method = $this->multiple ? 'append' : 'html';
            $_id = $this->multiple ? "var id = '{$id}_'+i+'';" : "var id = '{$id}';";
            $_name = $this->multiple ? "var name = '{$name}['+i+']';" : "var name = '{$name}';";

            $template = CJavaScript::encode(Yii::app()->controller->renderPartial('application.components.inputs.views.FileManagerFile', array(
                        'ownerId' => $id,
                        'ownerName' => $name,
                        'multiple' => $this->multiple,
                        'result' => '%form%',
                        'value' => '%filename%',
                        'basename' => '%basename%',
                        'filesize' => '%filesize%',
                    ), true)
            );
            $config =  base64_encode(serialize($this->element));
            $multiple = intval($this->multiple);

            $this->options['getFileCallback'] = 'js:'.<<<DATA
function(data, fm) {

    var template = {$template};

    if (typeof data[0] == "undefined") {
        data = [data];
    }
    var filename;
    for (var kk in data) {

        filename = data[kk];

        var i = $('.{$id}_fileform').length;
        {$_id}
        {$_name}
        var basename = cmsFileBaseName(filename.url);
        var thumbnail = '/images/icons/fatcow/32x32/document_empty.png';
        var iconWidth = 32;
        if (filename.tmb) {
            thumbnail = filename.tmb;
            while (thumbnail.indexOf(fm.option('tmbUrl'))==0) {
                thumbnail = thumbnail.replace(fm.option('tmbUrl'), '');
            }
            thumbnail = fm.option('tmbUrl') + thumbnail;
            iconWidth = 48;
        }
        var filesize = cmsReadableFileSize(filename.size);
        if (filename.width && filename.height) {
            filesize += '<br />'+filename.width+'&times;'+filename.height;
        }
        if ((i==0 && {$multiple}==0) || {$multiple}) {
            $.ajax({
                url: '/?r=records/fields&id='+id+'_data&name='+name+'[data]',
                async: false,
                data: {
                    'config': '{$config}',
                    '{$csrfTokenName}': '{$csrfToken}'
                },
                cache: false,
                type: 'post',
                success: function(result) {
                    html = template
                        .replace(/%form%/g, result)
                        .replace(/%filename%/g, filename.url)
                        .replace(/%name%/g, name)
                        .replace(/%basename%/g, basename)
                        .replace(/%id%/g, id)
                        .replace(/%filesize%/g, filesize);
                    $('#{$id}_filelist').append(html);
                    $('#'+id+'_filename').siblings('.{$id}_fileimage').attr('src', thumbnail).width(iconWidth);
                }
            });
        } else {
            $('#'+id+'_filename').val(filename.url);
            $('#'+id+'_filename').siblings('.{$id}_filename').text(basename);
            $('#'+id+'_filename').siblings('.{$id}_filesize').html(filesize);
            $('#'+id+'_filename').siblings('.{$id}_fileimage').attr('src', thumbnail).width(iconWidth);
        }
        cmsDialogResize($('.{$id}_fileform'));
    }
}
DATA;

        }

        // Интеграция с Tempo для закачки в директории согласно дате
        if (Yii::app()->request->getParam('datedir')) {
            $this->options['customData']['datedir'] = true;
        }

        $this->options['customData']['mode'] = Yii::app()->request->getParam('mode', '');

        $this->options['width'] = (!$this->width || $this->width == 'auto')
            ? "Math.max(Math.ceil($('body').width()*0.8),1000)"
            : intval($this->width);
        $this->options['height'] = (!$this->height || $this->height == 'auto')
            ? 500
            : intval($this->height);

        $options = CJavaScript::encode($this->options);

        $cs->registerScript('FileManager#'.$id,"var elf = $('#{$id}_fm').elfinder({$options}).elfinder('instance');");

    }

}