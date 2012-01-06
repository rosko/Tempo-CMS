<?php

class TextEditor extends CInputWidget
{
    public $kind = 'ckeditor';
    public $toolbarSet = 'CMS';
    public $config = array();
    public $skin = 'default';
    public $width = 800;
    public $height = 300;

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

        $kind = strtolower($this->kind);
        if ($kind == 'fckeditor' || $kind == 'fck')
            $this->useFckeditor();
        else
            $this->useCkeditor();

    }
    
    public function useFckeditor()
    {
        $id = $this->htmlOptions['id'];
        $name = $this->htmlOptions['name'];

        $cs=Yii::app()->getClientScript();

        $am=Yii::app()->getAssetManager();
        $fckeditorPath = $am->publish(Yii::getPathOfAlias('application.vendors.fckeditor'));
        $jsUrl = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.assets.js'));
        $themeBaseUrl = Yii::app()->theme->baseUrl;

        $cs->registerScriptFile($fckeditorPath.'/fckeditor.js');
        $baseUrl = Yii::app()->baseUrl;
        $value = $this->hasModel() ? $this->model{$this->attribute} : $this->value;
        $lang = Yii::app()->language;
        $js = <<<JS

//$('#{$id}_editorbtn').click(function() {
//$(function(){

        var oFCKeditor = new FCKeditor('{$name}') ;
        var basePath = '{$fckeditorPath}/';
        oFCKeditor.BasePath = basePath;
        oFCKeditor.Width = '{$this->width}';
        oFCKeditor.Height = '{$this->height}';
        oFCKeditor.ToolbarSet = '{$this->toolbarSet}';
        var value = document.getElementById('{$id}_value');
        oFCKeditor.Value = value.value;
        oFCKeditor.Config["CustomConfigurationsPath"] = "{$jsUrl}/fckconfig.js";
        oFCKeditor.Config["EditorAreaCSS"] = '{$themeBaseUrl}/css/main.css';
        oFCKeditor.Config['AutoDetectPasteFromWord'] = true;
        oFCKeditor.Config['LinkDlgHideTarget'] = true;
        oFCKeditor.Config['MaxUndoLevels'] = 100;
        oFCKeditor.Config['TemplateReplaceAll'] = false;
        oFCKeditor.Config['FontFormats'] = 'p;h1;h2;h3;h4;h5;h6';
        oFCKeditor.Config['RemoveFormatTags'] = 'code,del,dfn,div,font,ins,kbd,q,samp,span,tt,var,dl,dt,dd,form,input,button,textarea,label';
        oFCKeditor.Config['EnterMode'] = 'p';
        oFCKeditor.Config['FillEmptyBlocks'] = true;
        oFCKeditor.Config['IgnoreEmptyParagraphValue'] = true;
        oFCKeditor.Config['ShiftEnterMode'] = 'br';
        oFCKeditor.Config['TabSpaces'] = 4;
        oFCKeditor.Config['FlashDlgHideAdvanced'] = true;
        oFCKeditor.Config['ImageDlgHideAdvanced'] = true;
        oFCKeditor.Config['LinkDlgHideAdvanced'] = true;
        oFCKeditor.Config['SkinPath'] =  basePath+'editor/skins/{$this->skin}/';
        oFCKeditor.Config['AllowQueryStringDebug'] = false;
        oFCKeditor.Config['FirefoxSpellChecker'] = true;
        oFCKeditor.Config['DefaultLanguage'] = '{$lang}';

        var div = document.getElementById('{$id}_editor');
        div.innerHTML = oFCKeditor.CreateHtml();
        div.style.overflow = 'auto';
        //$('#{$id}_editorbtn').hide();

//});

JS;

        foreach ($this->config as $k => $v) {
            $v = CJavaScript::encode($v);
            $js .= "  oFCKeditor->Config['{$k}'] = {$v};\n";
        }

        $js .= <<<JS
$('form').submit(function() {
    FCKeditorAPI.GetInstance('{$name}').UpdateLinkedField();
/*    var instanceName = '{$name}';
    var instance = FCKeditorAPI.GetInstance(instanceName);
        instance.UpdateLinkedField();
        var instanceScope = instance.EditingArea.Window.parent;
        instanceScope.FCKTools.RemoveEventListener(instance.GetParentForm(), 'submit', instance.UpdateLinkedField);
        instanceScope.FCKTools.RemoveEventListener(instanceScope, 'unload', instanceScope.FCKeditorAPI_Cleanup);
        instanceScope.FCKTools.RemoveEventListener(instanceScope, 'beforeunload', instanceScope.FCKeditorAPI_ConfirmCleanup);
        if (jQuery.isFunction(instanceScope.FCKIECleanup_Cleanup)) {
            instanceScope.FCKIECleanup_Cleanup();
        }
        instanceScope.FCKeditorAPI_ConfirmCleanup();
        instanceScope.FCKeditorAPI_Cleanup();
        $('#' + instanceName + '___Config').remove();
        $('#' + instanceName + '___Frame').remove();
        $('#' + instanceName).show();*/
});

JS;
        $cs->registerScript('Yii.TextEditorFCK#'.$id,$js);

        echo '<div style="overflow:scroll;width:'.($this->width+20).'px;height:'.$this->height.'px;" id="' . $this->htmlOptions['id'] .'_editor">' . $value  . '</div>';
        echo '<textarea id="' . $this->htmlOptions['id'] .'_value" style="display:none;">' . $value . '</textarea>';

    }

    public function useCkeditor()
    {
        $id = $this->htmlOptions['id'];
        $name = $this->htmlOptions['name'];

        $cs=Yii::app()->getClientScript();

        $am=Yii::app()->getAssetManager();
        $CkeditorPath = $am->publish(Yii::getPathOfAlias('application.vendors.ckeditor'));
        $baseUrl = Yii::app()->baseUrl;
        $jsUrl = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.assets.js'));
        $themeBaseUrl = Yii::app()->theme->baseUrl;

        $js = <<<JS
        var CKEDITOR_BASEPATH = '{$CkeditorPath}/';
JS;
        $cs->registerScript('Yii.TextEditorCK#pre'.$id,$js,CClientScript::POS_HEAD);

        $cs->registerScriptFile($CkeditorPath.'/ckeditor.js');
        $value = $this->hasModel() ? $this->model{$this->attribute} : $this->value;
        $lang = Yii::app()->language;

        $this->config['baseHref'] = $baseUrl.'/';
        $defaultConfig = array(
            'language'=>$lang,
            'skin'=>'v2',
            'contentCss'=>$themeBaseUrl.'/css/main.css',
            'templates_replaceContent'=>false,
            'enterMode'=>'js:CKEDITOR.ENTER_P',
            'shiftEnterMode'=>'js:CKEDITOR.ENTER_BR',
            'tabSpaces'=>4,
            'ignoreEmptyParagraph'=>true,
            'disableNativeSpellChecker'=>false,
            'fillEmptyBlocks'=>true,
            'removeFormatTags'=>'code,del,dfn,div,font,ins,kbd,q,samp,span,tt,var,dl,dt,dd,form,input,button,textarea,label',
            'removeDialogTabs'=>'flash:advanced;image:advanced;link:advanced;image:Link',
            'format_tags'=>'p;h1;h2;h3;h4;h5;h6',
            'forceEnterMode'=>true,
            'forceSimpleAmpersand'=>true,
            'width'=>$this->width,
            'height'=>$this->height,
            'toolbar'=>'Full',
            'toolbar_Basic'=>"js:[
['Bold','Italic','Strike','-','Subscript','Superscript','-',
'NumberedList','BulletedList','-',
'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
['Undo','Redo','-','RemoveFormat','-',
'Link','Unlink','-','Table','-','Paste','PasteText','PasteFromWord','-',
'TextColor','Outdent','Indent','Blockquote','-','Source']
]",

            'toolbar_Full'=>"js:[
['Bold','Italic','Strike'],
['Format'],
['NumberedList','BulletedList','-','Subscript','Superscript','-','Outdent','Indent','Blockquote'],
['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
['Source','Templates'],
'/',
['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
['Link','Unlink','Anchor'],
['Image','Flash','Table','HorizontalRule','SpecialChar'],
['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print'],
['TextColor','BGColor'],
['ShowBlocks']
]",
            
            
        );
        foreach ($defaultConfig as $k => $v) {
            if (!isset($this->config[$k]))
                $this->config[$k] = $v;
        }
        $conf = CJavaScript::encode($this->config);

        $js = <<<JS

var {$id}_e = CKEDITOR.replace('{$id}_editor', {$conf});

$('form').submit(function() {
    if ({$id}_e) {
        $('#{$id}').val({$id}_e.getData());
    }
});

JS;
        $cs->registerScript('Yii.TextEditorCK#'.$id,$js);

        echo '<div style="width:'.($this->width).'px;height:'.$this->height.'px;" id="' . $id .'_editor">' . $value  . '</div>';
        echo '<textarea id="' . $id .'" name="'.$name.'" style="display:none;">' . $value . '</textarea>';

    }

}