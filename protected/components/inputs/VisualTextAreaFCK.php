<?php

class VisualTextAreaFCK extends CInputWidget
{
    public $width = 800;
    public $height = 400;
    public $toolbarSet = 'CMS';
    public $config = array();
    public $skin = 'default';
    
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

        //include_once Yii::app()->basePath . '/../www/3rdparty/fckeditor/fckeditor.php';

        $this->registerClientScript();
        
        $value = $this->hasModel() ? $this->model{$this->attribute} : $this->value;
        //echo CHtml::button('Включить редактор', array(
        //    'id' => $id . '_editorbtn'
        //));
        echo '<div style="overflow:scroll;width:'.($this->width+20).'px;height:'.$this->height.'px;" id="' . $this->htmlOptions['id'] .'_editor">' . $value  . '</div>';
        echo '<textarea id="' . $this->htmlOptions['id'] .'_value" style="display:none;">' . $value . '</textarea>';
/*
        $oFCKeditor = new FCKeditor($this->htmlOptions['name']) ;
        $basePath = Yii::app()->baseUrl . '/3rdparty/fckeditor/';
        $oFCKeditor->BasePath = $basePath;
        $oFCKeditor->Width = $this->width;
        $oFCKeditor->Height = $this->height;
        $oFCKeditor->ToolbarSet = $this->toolbarSet;
        $oFCKeditor->Value = $this->hasModel() ? $this->model{$this->attribute} : $this->value;
        $oFCKeditor->Config["CustomConfigurationsPath"] = Yii::app()->baseUrl."/js/fckconfig.js?" . time();
        $oFCKeditor->Config["EditorAreaCSS"] = Yii::app()->baseUrl.'/css/main.css';
        $oFCKeditor->Config['AutoDetectPasteFromWord'] = true;
        $oFCKeditor->Config['LinkDlgHideTarget'] = true;
        $oFCKeditor->Config['MaxUndoLevels'] = 100;
        $oFCKeditor->Config['TemplateReplaceAll'] = false;
        $oFCKeditor->Config['FontFormats'] = 'p;h1;h2;h3;h4;h5;h6';
        $oFCKeditor->Config['RemoveFormatTags'] = 'code,del,dfn,div,font,ins,kbd,q,samp,span,tt,var,dl,dt,dd,form,input,button,textarea,label';
        $oFCKeditor->Config['EnterMode'] = 'p';
        $oFCKeditor->Config['FillEmptyBlocks'] = true;
        $oFCKeditor->Config['IgnoreEmptyParagraphValue'] = true;
        $oFCKeditor->Config['ShiftEnterMode'] = 'br';
        $oFCKeditor->Config['TabSpaces'] = 4;
        $oFCKeditor->Config['FlashDlgHideAdvanced'] = true;
        $oFCKeditor->Config['ImageDlgHideAdvanced'] = true;
        $oFCKeditor->Config['LinkDlgHideAdvanced'] = true;
        $oFCKeditor->Config['SkinPath'] = $basePath . 'editor/skins/' . $this->skin . '/';
        $oFCKeditor->Config['AllowQueryStringDebug'] = false;
        $oFCKeditor->Config['FirefoxSpellChecker'] = true;
        $oFCKeditor->Config['DefaultLanguage'] = 'ru';

        foreach ($this->config as $k => $v) {
            $oFCKeditor->Config[$k] = $v;
        }
        
        $oFCKeditor->Create() ;
*/
    }
    
    public function registerClientScript()
    {
        $id = $this->htmlOptions['id'];
        $name = $this->htmlOptions['name'];

        $cs=Yii::app()->getClientScript();

        $cs->registerScriptFile('/3rdparty/fckeditor/fckeditor.js');
        $baseUrl = Yii::app()->baseUrl;
        $value = $this->hasModel() ? $this->model{$this->attribute} : $this->value;
        $js = <<<EOD
        
//$('#{$id}_editorbtn').click(function() {
//$(function(){

        var oFCKeditor = new FCKeditor('{$name}') ;
        var basePath = '{$baseUrl}/3rdparty/fckeditor/';
        oFCKeditor.BasePath = basePath;
        oFCKeditor.Width = '{$this->width}';
        oFCKeditor.Height = '{$this->height}';
        oFCKeditor.ToolbarSet = '{$this->toolbarSet}';
        var value = document.getElementById('{$id}_value');
        oFCKeditor.Value = value.value;
        oFCKeditor.Config["CustomConfigurationsPath"] = "{$baseUrl}/js/fckconfig.js";
        oFCKeditor.Config["EditorAreaCSS"] = '{$baseUrl}/css/main.css';
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
        oFCKeditor.Config['DefaultLanguage'] = 'ru';

        var div = document.getElementById('{$id}_editor');
        div.innerHTML = oFCKeditor.CreateHtml();
        div.style.overflow = 'auto';
        //$('#{$id}_editorbtn').hide();

//});

EOD;

        foreach ($this->config as $k => $v) {
            $v = CJavaScript::encode($v);
            $js .= "  oFCKeditor->Config['{$k}'] = {$v};\n";
        }
        
        $js .= <<<EOD
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

EOD;
        $cs->registerScript('Yii.VisualTextAreaFCK#'.$id,$js);
        
        
    }
}

?>