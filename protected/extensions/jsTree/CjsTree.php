<?php

class CJsTree extends CWidget
{
	public $htmlOptions;
	public $cssFile;
	public $data;

	public $baseUrl;	// jsTree install folder. registering scripts & css's under this folder.   
	public $body;		// jsTree Html data source. 

	public $core;
	public $plugins;
	public $html_data;
	public $json_data;
	public $xml_data;
	public $themes;
	public $ui;
	public $crrm;
	public $hotkeys;
	public $languages;
	public $cookies;
	public $sort;
	public $dnd;
	public $checkbox;
	public $search;
	public $contextmenu;
	public $types;
	public $themeroller;
	public $unique;
	
	public $events = array();
	
	/**
	 * Initializes the widget.
	 * This method registers all needed client scripts and renders
	 * the tree view content.
	 */
    public function init()
    {
        if(isset($this->htmlOptions['id']))
            $id=$this->htmlOptions['id'];
        else
            $id=$this->htmlOptions['id']=$this->getId();
//        if($this->url!==null)
//            $this->url=CHtml::normalizeUrl($this->url);

        $dir = dirname(__FILE__).DIRECTORY_SEPARATOR.'source';
        $this->baseUrl = Yii::app()->getAssetManager()->publish($dir);

        $cs=Yii::app()->getClientScript();
        //$cs->registerScriptFile($this->baseUrl.'/jquery.tree.js');
        $cs->registerScriptFile($this->baseUrl.'/jquery.jstree.js');

        $options=$this->getClientOptions();
        $options=$options===array()?'{}' : CJavaScript::encode($options);
		$events = $this->getClientEvents();
        $cs->registerScript('Yii.CJsTree#'.$id,"jQuery(function () { jQuery(\"#{$id}\").jstree($options)$events; });");
        if($this->cssFile !== null && $this->cssFile !== false)
            $cs->registerCssFile($this->cssFile);
			
		echo CHtml::tag('div', $this->htmlOptions,false,false)."\n";
		echo "<ul>";
    }

	/**
	 * Ends running the widget.
	 */
	public function run()
	{
		echo self::saveDataAsHtml($this->data);
		echo "</ul></div>";
	}

	/**
	 * @return array the javascript options
	 */
	protected function getClientOptions()
	{
		$options=array();
		foreach(array('core','plugins','html_data','json_data','xml_data','themes','ui','crrm','hotkeys','languages','cookies','sort','dnd','checkbox','search','contextmenu','types','themeroller','unique') as $name)
		{
			if($this->$name!==null)
				$options[$name]=$this->$name;
		}
		return $options;
	}
	
	protected function getClientEvents()
	{
		$events = '';
		foreach ($this->events as $name => $js)
		{
			if (substr($js,0,3)=='js:') $js=substr($js,3);
			$events .= '.bind("'.$name.'", '.$js.')';
		}
		return $events;
	}
	
	/**
	 * Generates tree view nodes in HTML from the data array.
	 * @param array the data for the tree view (see {@link data} for possible data structure).
	 * @return string the generated HTML for the tree view
	 */
	public static function saveDataAsHtml($data)
	{
		$html='';
		if(is_array($data))
		{
			foreach($data as $node)
			{
				if(!isset($node['text']))
					continue;
				//$node['data']=$node['text'];
				$id=isset($node['id']) ? (' id="'.$node['id'].'"') : '';
				if(isset($node['expanded']))
					$css=$node['expanded'] ? 'open' : 'closed';
				else
					$css='';
				if(isset($node['hasChildren']) && $node['hasChildren'])
				{
					if($css!=='')
						$css.=' ';
					$css.='hasChildren';
				}
				if($css!=='')
					$css=' class="'.$css.'"';
				if(isset($node['rel']))
					$css=$css.' rel="'.$node['rel'].'"';
				$html.="<li{$id}{$css}>{$node['text']}";
				if(isset($node['children']))
				{
					$html.="\n<ul>\n";
					$html.=self::saveDataAsHtml($node['children']);
					$html.="</ul>\n";
				}
				$html.="</li>\n";
			}
		}
		return $html;
	}

	/**
	 * Saves tree view data in JSON format.
	 * This method is typically used in dynamic tree view loading
	 * when the server code needs to send to the client the dynamic
	 * tree view data.
	 * @param array the data for the tree view (see {@link data} for possible data structure).
	 * @return string the JSON representation of the data
	 */
	public static function saveDataAsJson($data)
	{
		if(empty($data))
			return '[]';
		else
			return CJavaScript::jsonEncode($data);
	}
}
