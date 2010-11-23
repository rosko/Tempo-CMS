<?php
/**
 * Smarty view renderer
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @link http://code.google.com/p/yiiext/
 * @link http://www.smarty.net/
 *
 * @version 0.9.7
 */
class ESmartyViewRenderer extends CApplicationComponent implements IViewRenderer {
    public $fileExtension='.tpl';
    public $filePermission=0755;
    public $pluginsDir = null;
    public $configDir = null;

    private $smarty;

    /**
     * Component initialization
     */
    function init(){
        // needed by Smarty 3
        define('SMARTY_SPL_AUTOLOAD', 1);

        Yii::import('application.vendors.*');
        spl_autoload_register('spl_autoload', true, true);
        require_once('Smarty/Smarty.class.php');

        $this->smarty = new Smarty();

        $this->smarty->template_dir = '';
        $compileDir = Yii::app()->getRuntimePath().'/smarty/compiled/';

        // create compiled directory if not exists
        if(!file_exists($compileDir)){
            mkdir($compileDir, $this->filePermission, true);
        }

        $this->smarty->compile_dir = $compileDir;


        $this->smarty->plugins_dir[] = Yii::getPathOfAlias('application.extensions.smarty');
        if(!empty($this->pluginsDir)){
            $this->smarty->plugins_dir[] = Yii::getPathOfAlias($this->pluginsDir);
        }

        if(!empty($this->configDir)){
            $this->smarty->config_dir = Yii::getPathOfAlias($this->configDir);
        }

        $this->smarty = $this->sysAssign($this->smarty);
    }

    /**
	 * Renders a view file.
	 * This method is required by {@link IViewRenderer}.
	 * @param CBaseController the controller or widget who is rendering the view file.
	 * @param string the view file path
	 * @param mixed the data to be passed to the view
	 * @param boolean whether the rendering result should be returned
	 * @return mixed the rendering result, or null if the rendering result is not needed.
	 */
	public function renderFile($context,$sourceFile,$data,$return) {
        // current controller properties will be accessible as {this.property}
        //$data['this'] = $context;

        // check if view file exists
        if(!is_file($sourceFile) || ($file=realpath($sourceFile))===false)
            throw new CException(Yii::t('yiiext','View file "{file}" does not exist.', array('{file}'=>$sourceFile)));

        //assign data
        $tpl = $this->sysAssign($this->smarty->createTemplate($sourceFile));
		$tpl->assign($data);

        //render
        try {
            $ret = $tpl->fetch($sourceFile);
        } catch (Exception $e) {
            $ret = '! Синтаксическая ошибка в шаблоне';
        }

        return $ret;
	}

    private function sysAssign($obj)
    {
        $obj->assign("TIME", sprintf('%0.5f',Yii::getLogger()->getExecutionTime()));
        $obj->assign("MEMORY", round(Yii::getLogger()->getMemoryUsage()/(1024*1024),2)." MB");
        //$obj->assign('dateFormatter', Yii::app()->dateFormatter);
        //$obj->assign('Yii', Yii::app());
        return $obj;
    }
}
