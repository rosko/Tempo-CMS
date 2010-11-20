<?php
$cs = Yii::app()->getClientScript();
$cs->setCoreScriptUrl('/js/empty/');

$tree_id = $tree_id ? $tree_id : 'pagetree';
$this->beginWidget('ext.jsTree.CjsTree', array(
    'id' => $tree_id,
    'core' => array(
        'initially_open'=>array($tree_id.'-1'),
        'animation'=>0,
        'strings'=>array(
            'loading'=>'Загрузка ...',
            'new_node'=>'Новая страница'
        ),
    ),
    'plugins'=>array(
        'html_data', 'themes', 'ui', 'crrm', 'hotkeys', 'cookie', 'types',
        ($multiple ? 'checkbox' : ''), ($multiple ? 'contextmenu' : '')
    ),
    'themes' => array(
        'dots'=>true,
        'theme'=>'default'
    ),
    'ui'=>array(
        'select_limit'=>1,
    ),
    'hotkeys'=>array(
        "insert" => 'js:function () {  }',
        'ctrl+x' => 'js:function () {  }',
        'ctrl+c' => 'js:function () {  }',
        'ctrl+v' => 'js:function () {  }',
        'del' => 'js:function() {  }',
        'return' => 'js:function(e) {
            var o = this.data.ui.last_selected || -1;
            this._get_node(o).children("a:eq(0)").dblclick();
            e.stopImmediatePropagation();
            return false; 
        }',
        "up" => ($multiple ? false : 'js:function (e) { 
            var o = this.data.ui.last_selected || -1;
            this._get_prev(o).children("a:eq(0)").click();
            e.stopImmediatePropagation();
            return false; 
        }'),
        "down" => ($multiple ? false : 'js:function (e) { 
            var o = this.data.ui.last_selected || -1;
            this._get_next(o).children("a:eq(0)").click();
            e.stopImmediatePropagation();
            return false;
        }'),
        "left" => ($multiple ? false : 'js:function (e) { 
            var o = this.data.ui.last_selected;
            if(o) {
                if(o.hasClass("jstree-open")) { this.close_node(o); }
                else { if (this._get_prev(o)) this._get_prev(o).children("a:eq(0)").click(); }
            }
            e.stopImmediatePropagation();
            return false;
        }'),
        "right" => ($multiple ? false : 'js:function (e) { 
            var o = this.data.ui.last_selected;
            if(o && o.length) {
                if(o.hasClass("jstree-closed")) { this.open_node(o); }
                else { if (this._get_next(o)) this._get_next(o).children("a:eq(0)").click(); }
            }
            e.stopImmediatePropagation();
            return false;
        }'),
    ),
    'contextmenu'=> ($multiple ? array(
        'select_node'=>true,
        'items' => array (
            'check_node_all' => array (
                'label' => "Отметить все дочерние страницы", 
                'icon' => 'create',
                'action' => <<<EOD
js:function(obj) {
    $(obj).children('a:eq(0)').dblclick();
}
EOD
,
            ),
            'create' => false,
            'rename' => false,
            'remove' => false,
            'ccp' => false,
        )
    ) : ''),
    'types'=>array(
        'valid_children'=>array('mainpage'),
        'types'=>array(
            'disabled'=>array(
                'valid_children'=>array(),
                'select_node'=>false,
                'hover_node'=>false,
				"start_drag" => false,
				"move_node" => false,
				"delete_node" => false,
				"remove" => false,
                "check_node" => false,
                "uncheck_node" => false,
                "dehover_node"=>false,
            ),
            'default'=>array(
                'valid_children'=>array('default'),
                'hover_node'=>($multiple ? true : false),
            ),
            'mainpage'=>array(
                "valid_children"=>array('default'),
                'hover_node'=>($multiple ? true : false),
				"start_drag" => false,
				"move_node" => false,
				"delete_node" => false,
				"remove" => false,
            )
        )
    ),
/*    'events'=>array(
        'loaded.jstree'=> <<<EOD
js:function(e) {
    alert('d');
//    var id = $(e.target).attr('rel');
//    var title = $(e.target).text();
//    alert(title);
//    alert(data.rslt.obj.attr("id"));
//    e.stopImmediatePropagation(); return false;
}
EOD
,
    )*/
    ));?>
<?php
function showBranch($tree, $path, $tree_id, $enabledOnly, $disabled) {
    if ($tree)
	foreach ($tree[$path] as $page) {
        $dsbl = (is_array($enabledOnly) && !in_array($page['id'], $enabledOnly)) ||
                    (is_array($disabled) && in_array($page['id'], $disabled));
        if ($dsbl)
        {
            $data = ' rel="disabled"';
        } else {
            $data = ($page['id'] == 1) ?  ' rel="mainpage"' : '';
        }
		echo '<li id="'.$tree_id.'-' . $page['id'] . '" ' . $data . ' ><a rev="page" rel="'.$page['id'].'" href="#"><ins>&nbsp;</ins>'.($dsbl ? '<s>' : '') . $page['title'] .($dsbl ? '</s>' : '')."</a>\n";
		if ($tree[$path.','.$page['id']]) {
			echo "<ul>\n";
			showBranch($tree, $path.','.$page['id'], $tree_id, $enabledOnly, $disabled);
			echo "</ul>\n";
		}
		echo "</li>\n";
	}
}
showBranch($tree, 0, $tree_id, $enabledOnly, $disabled);
?>
<?php $this->endWidget();?>