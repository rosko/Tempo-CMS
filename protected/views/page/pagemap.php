<img style="float:left;margin-right:1em;" valign="baseline" src="/images/icons/fatcow/32x32/sitemap.png" />
<h3><?=Yii::t('cms', 'Sitemap')?></h3>
<p><?=Yii::t('cms', 'Click twice on page to go')?><br />
<a href="#" class="cms-btn-pagemap-openall"><?=Yii::t('cms', 'Expand all')?></a> / <a href="#" class="cms-btn-pagemap-closeall"><?=Yii::t('cms', 'Collapse all')?></a>
</p>
<?php

    $cs=Yii::app()->getClientScript();
    $cs->registerScript('pagemap', <<<EOD
$(function() {
    $('.cms-btn-pagemap-openall').click(function() {
        $('#pagemap').jstree('open_all');
        return false;
    });
    $('.cms-btn-pagemap-closeall').click(function() {
        $('#pagemap').jstree('close_all');
        return false;
    });
});
EOD
);


	if (!Yii::app()->request->isAjaxRequest) {
?>

<div class="hidden">
    <div id="cms-page-delete" class="cms-splash">
    </div>
</div>

<div class="top fixed cms-panel" id="cms-info"></div>

<?php
        $jsChildrenDelete = 'location.reload();';
    } else {
        $jsChildrenDelete = '';
    }

// В будущем нужно сделать возможность создания, переименования, перемещения, удаления страниц

$this->beginWidget('ext.jsTree.CjsTree', array(
    'id' => 'pagemap',
    'core' => array(
        'initially_open'=>$initially_open,
        'animation'=>0,
        'strings'=>array(
            'loading'=>Yii::t('cms', 'Loading ...'),
            'new_node'=>Yii::t('cms', 'New page'),
        ),
    ),
    'plugins'=>array(
        'html_data', 'themes', 'ui', 'crrm', 'hotkeys', 'cookie', 'dnd', 'contextmenu', 'types',
    ),
    'themes' => array(
        'dots'=>true,
        'theme'=>'default'
    ),
    'ui'=>array(
        'select_limit'=>1,
    ),
    'hotkeys'=>array(
        "insert" => 'js:function (e) {
            this.create(this.data.ui.last_selected, "inside");
            e.stopImmediatePropagation();
            return false; 
        }',
        'ctrl+x' => 'js:function (e) {
            this.cut(this.data.ui.last_selected);
            e.stopImmediatePropagation();
            return false; 
        }',
//        'ctrl+c' => 'js:function (e) {
//            this.copy(this.data.ui.last_selected);
//            e.stopImmediatePropagation();
//            return false; 
//        }',
        'ctrl+v' => 'js:function (e) {
            this.paste(this.data.ui.last_selected);
            e.stopImmediatePropagation();
            return false; 
        }',
        'ctrl+up' => 'js:function (e) {
            var obj = this.data.ui.hovered || this.data.ui.last_selected || -1;
            if (this._get_prev(obj))
                this.move_node(obj, this._get_prev(obj), "before");
            e.stopImmediatePropagation();
            return false;
        }',
        'ctrl+down' => 'js:function (e) {
            var obj = this.data.ui.hovered || this.data.ui.last_selected || -1;
            if (this._get_next(obj))
                this.move_node(obj, this._get_next(obj), "after");
            e.stopImmediatePropagation();
            return false;
        }',
        'shift+up' => 'js:function (e) {
            var obj = this.data.ui.hovered || this.data.ui.last_selected || -1;
            if (this._get_prev(obj))
                this.move_node(obj, this._get_prev(obj), "before");
            e.stopImmediatePropagation();
            return false;
        }',
        'shift+down' => 'js:function (e) {
            var obj = this.data.ui.hovered || this.data.ui.last_selected || -1;
            if (this._get_next(obj))
                this.move_node(obj, this._get_next(obj), "after");
            e.stopImmediatePropagation();
            return false;
        }',
        'ctrl+left' => 'js:function (e) {
            var obj = this.data.ui.hovered || this.data.ui.last_selected || -1;
            if (this._get_parent(obj))
                this.move_node(obj, this._get_parent(obj), "after");
            e.stopImmediatePropagation();
            return false;
        }',
        'ctrl+right' => 'js:function (e) {
            var obj = this.data.ui.hovered || this.data.ui.last_selected || -1;
            if (this._get_prev(obj))
                this.move_node(obj, this._get_prev(obj), "inside");
            e.stopImmediatePropagation();
            return false;
        }',
        'shift+left' => 'js:function (e) {
            var obj = this.data.ui.hovered || this.data.ui.last_selected || -1;
            if (this._get_parent(obj))
                this.move_node(obj, this._get_parent(obj), "after");
            e.stopImmediatePropagation();
            return false;
        }',
        'shift+right' => 'js:function (e) {
            var obj = this.data.ui.hovered || this.data.ui.last_selected || -1;
            if (this._get_prev(obj))
                this.move_node(obj, this._get_prev(obj), "inside");
            e.stopImmediatePropagation();
            return false;
        }',
        'del' => 'js:function(e) {
            this.remove(this.data.ui.last_selected);
            e.stopImmediatePropagation();
            return false; 
        }',
        "return" => 'js:function (e) {
            var id = $(this.data.ui.last_selected).find("a").eq(0).attr("rel");
            var title = $(e.target).text();
            if (id) {
                location.href = "/?r=page/view&id="+id+"&language="+$.data(document.body, "language");
            }
            e.stopImmediatePropagation(); return false;
        }',
        "up" => 'js:function (e) { 
            var o = this.data.ui.last_selected || -1;
            if (this._get_prev(o))
                this._get_prev(o).children("a:eq(0)").click();
            e.stopImmediatePropagation();
            return false; 
        }',
        "down" => 'js:function (e) { 
            var o = this.data.ui.last_selected || -1;
            if (this._get_next(o))
                this._get_next(o).children("a:eq(0)").click();
            e.stopImmediatePropagation();
            return false;
        }',
        "left" => 'js:function (e) { 
            var o = this.data.ui.last_selected;
            if(o) {
                if(o.hasClass("jstree-open")) { this.close_node(o); }
                else { if (this._get_prev(o)) this._get_prev(o).children("a:eq(0)").click(); }
            }
            e.stopImmediatePropagation();
            return false;
        }',
        "right" => 'js:function (e) { 
            var o = this.data.ui.last_selected;
            if(o && o.length) {
                if(o.hasClass("jstree-closed")) { this.open_node(o); }
                else { if (this._get_next(o)) this._get_next(o).children("a:eq(0)").click(); }
            }
            e.stopImmediatePropagation();
            return false;
        }',
    ),
    'dnd'=>array(
        'copy_modifier' => '',
    ),
    'contextmenu'=>array(
        'select_node'=>true,
        'items' => array (
            'create' => array (
                'label' => Yii::t('cms', 'Create') . " (Ins)",
                'icon' => 'create',
                'action' => 'js:function (obj) { this.create(obj, "inside");  }',
                'separator_after' => true,
            ),
            'rename' => array(
                'label' => Yii::t('cms', 'Rename') . " (F2)",
                'icon' => "rename",
                'action' => 'js:function (obj) { this.rename(obj); }', 
            ),
            'remove' => array(
                'label' => Yii::t('cms', 'Remove') . " (Del)",
                'icon' => "remove",
                'action' => 'js:function (obj) { this.remove(obj); }', 
                'separator_after' => true,
            ),
            'ccp' => false,
            'cut' => array(
                'label' => Yii::t('cms', 'Cut') . ' (Ctrl+X)',
                'icon' => 'cut',                    
                'action' => 'js:function (obj) { this.cut(obj); }', 
            ),
//            'copy' => array(
//                'label' => Yii::t('cms', 'Copy') . ' (Ctrl+C)',
//                'icon' => 'copy',
//                'action' => 'js:function (obj) { this.copy(obj); }', 
//            ),
            'paste' => array(
                'label' => Yii::t('cms', 'Paste') . ' (Ctrl+V)',
                'icon' => 'paste',                    
                'action' => 'js:function (obj) { this.paste(obj); }', 
            )
        ),
    ),
    'types'=>array(
        'valid_children'=>array('mainpage'),
        'types'=>array(
            'default'=>array(
                'valid_children'=>array('default'),
                //"select_node"=> 'js:function() {alert("select");return true;} ',
                'hover_node'=>false,
//                "open_node"=>false,
/*                "create_node"=> <<<EOD
js:function(e, data) {
    var obj = this.data.ui.hovered ||this.data.ui.last_selected;
    var id = $(obj).attr('id');
    //alert(id);
    return true;
}

EOD
,*/
//                "delete_node"=>
            ),
            'mainpage'=>array(
                "valid_children"=>array('default'),
				"start_drag" => false,
				"move_node" => false,
				"delete_node" => false,
				"remove" => false,
                'hover_node'=>false,
                'open_node'=>true,
                'open_all'=>true
            )
        )
    ),
    'events'=>array(
        'dblclick.jstree'=> <<<EOD
js:function(e) {
    var id = $(e.target).attr('rel');
    if (id) {
        var title = $(e.target).text();
        location.href = '/?r=page/view&id='+id+'&language='+$.data(document.body, 'language');
    }
    e.stopImmediatePropagation();
    return false;
}
EOD
,
        'move_node.jstree' => <<<EOD
js:function(e, data) {
    var id = $(data.rslt.o).children('a:eq(0)').attr('rel');
    var parent_id = $(data.rslt.o).parents('li:eq(0)').children('a:eq(0)').attr('rel');
    var siblings = [];
    $(data.rslt.o).parent().children().each(function () {
        siblings.push($(this).children('a:eq(0)').attr('rel'));
    });
    var url = '/?r=page/pagesSort&id='+id+'&language='+$.data(document.body, 'language');
    var params = 'parent_id='+parent_id+'&'+decodeURIComponent($.param({'order': siblings}));
    ajaxSave(url, params, 'POST');
}
EOD
,
        'rename_node.jstree' => <<<EOD
js:function(e, data) {
    var id = $(data.rslt.obj).children('a:eq(0)').attr('rel');
    var parent_id = $(data.rslt.obj).parents('li:eq(0)').children('a:eq(0)').attr('rel');
    var siblings = [];
    $(data.rslt.obj).parent().children().each(function () {
        siblings.push($(this).children('a:eq(0)').attr('rel'));
    });
    var title = $(data.rslt.obj).children('a:eq(0)').text();
    
    var url = '/?r=page/pageRename&id='+id+'&language='+$.data(document.body, 'language');
    var params = 'parent_id='+parent_id+'&title='+trim(title)+'&'+decodeURIComponent($.param({'order': siblings}));
    ajaxSave(url, params, 'POST', function(html) {
        if (html != 0 && !id) {
            // Установить новому элементу указания на id страницы
            $(data.rslt.obj).attr('id', 'page-'+html);
            $(data.rslt.obj).children('a:eq(0)').attr({
                'rev': 'page',
                'rel': html
            });
        }
    });
}
EOD
,
        'create_node.jstree' => <<<EOD
js:function(e, data) {
    var id = $(data.rslt.o).children('a:eq(0)').attr('rel');
    var title = $(e.target).text();
//    alert(title);
}
EOD
,
        'delete_node.jstree' => <<<EOD
js:function(e, data) {
//    if (data.func == 'delete_node')
//    {
//        if(data.args[0]) {
//            var id = $(data.args[0]).children('a:eq(0)').attr('rel');
            var id = $(data.rslt.obj).children('a:eq(0)').attr('rel');
            pageDeleteDialog(id, function() {
                var url = '/?r=page/pageDelete&id='+id+'&language='+$.data(document.body, 'language');
                var params = 'deletechildren=1';
                ajaxSave(url, params, 'GET');
            }, function(html) {
                hideSplash();
                {$jsChildrenDelete}
            }, function() {
                $.jstree.rollback(data.rlbk);
            });
//        }
//        return false;
//    }
}
EOD
,
    ),
    ));
function showBranch($tree, $path) {
    if ($tree)
	foreach ($tree[$path] as $page) {
		$data = ($page['id'] == 1) ?  ' rel="mainpage"' : '';
		echo '<li id="page-' . $page['id']. '" ' . $data . ' ><a rev="page" rel="'.$page['id'].'" href="#'.$page['id'].'"><ins>&nbsp;</ins>' . $page['title'] . "</a>\n";
		if ($tree[$path.','.$page['id']]) {
			echo "<ul>\n";
			showBranch($tree, $path.','.$page['id']);
			echo "</ul>\n";
		}
		echo "</li>\n";
	}
}
showBranch($tree, 0);
?>
<?php $this->endWidget();?>
