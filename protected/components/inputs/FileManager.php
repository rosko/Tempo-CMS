<?php
class FileManager extends CInputWidget
{
    public $volume = 'files';

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

        if (isset($this->size))
            $this->htmlOptions['size'] = $this->size;

        $this->registerClientScript();

        if($this->hasModel())
            echo CHtml::activeHiddenField($this->model,$this->attribute,$this->htmlOptions);
        else
            echo CHtml::hiddenField($name,$this->value,$this->htmlOptions);

        $value = $this->hasModel() ? $this->model->{$this->attribute} : $this->value;

        $this->registerClientScript();
        $this->render('FileManager', array(
            'volume'=>$this->volume,
            'id'=>$id,
        ));
    }

    public function registerClientScript()
    {
        $cs=Yii::app()->getClientScript();
        $cs->registerCoreScript('jquery');
        $cs->registerCoreScript('jquery.ui');
        $jsPath = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.assets.js'));
        $jnotifyPath = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.vendors.jnotify'));

        $cs->registerScriptFile($jsPath.'/cms.js');
        $cs->registerScriptFile($jsPath.'/lib.js');
        $cs->registerScriptFile($jsPath.'/jquery.cookie.js');
        $cs->registerScriptFile($jsPath.'/jquery.hotkeys.js');

        $cs->registerScriptFile($jnotifyPath.'/jquery.jnotify.js');

    }
}