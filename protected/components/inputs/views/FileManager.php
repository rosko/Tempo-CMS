<!-- <label>Группировать по:</label>
<select>
    <option>Каталогам</option>
    <option>Типу файлов</option>
    <option>Дате</option>
    <option>Размеру</option>
    <option>Ключевым словам</option>
    <option>Страницам</option>
</select>
-->
<script type="text/javascript">
    var volume_<?=$id?> = '<?=$volume?>';
    var select_<?=$id?> = null;

    function pathToString(breadcrumbs)
    {
        var path="";
        for (i in breadcrumbs) {
            path = path + "<?=DIRECTORY_SEPARATOR?>" + breadcrumbs[i];
        }
        return path;
    }

    function getUniqueTitle(context,text,i,title)
    {
        if (!i) i=0;
        if (!title) title=text;
        s = $(context).find('li > a:contains('+title+')');
        if(s.length){
            i++;
            return getUniqueTitle(context,text,i,text + ' '+i)
        }else{
            return title;
        }
    }

        $('<div id="cms-statusbar"></div>').prependTo('body');
        $('<div id="cms-notification"></div>').prependTo('body');
            $('#cms-statusbar').jnotifyInizialize({
                oneAtTime: true
            });
            $('#cms-notification')
                .jnotifyInizialize({
                    oneAtTime: false,
                    appendType: 'append'
                })
                .css({ 'position': 'fixed',
                    'marginTop': '10px',
                    'left': Math.ceil($('body').width()/2-125)+'px',
                    'width': '300px',
                    'z-index': '100000'
                });
</script>
<?php
$strDoYouReally = Yii::t('fileManager', 'Do you really want to delete?');

$this->widget('ext.jsTree.CjsTree', array(
    'id'=>$id.'_filetree',
    'core'=>array(
        'strings'=>array(
            'loading'=>Yii::t('fileManager', 'Loading ...'),
            'new_node'=>Yii::t('fileManager', 'New folder'),
        ),
    ),
    'plugins'=>array(
        'themes','json_data','ui','crrm','dnd','hotkeys','cookies',
        'sort','contextmenu','types','unique',
    ),
    'sort'=>'js:function (a, b) {
        var at = $(a).attr("rel");
        var bt = $(b).attr("rel");
        if (at == "folder" && bt == "file") return -1;
        if (at == "file" && bt == "folder") return 1;
        return this.get_text(a) > this.get_text(b) ? 1 : -1;
    }',
    'json_data'=>array(
        'ajax'=>array(
            'url'=>'/?r=fileManager/fileList&volume='.$volume,
            'data'=> 'js:function(n) {
                return { "path": pathToString(this.get_path(n)) };
            }'
        )
    ),
    'themes'=>array(
        'dots'=>false,
        'theme'=>'apple',
    ),
    'types'=>array(
        'max_depth'=>-2,
        'max_children'=>-2,
        'types'=>array(
            'file'=>array(
                'valid_children'=>'none',
                'icon'=>array(
                    'image'=>'',
                ),
            ),
            'folder'=>array(
                'valid_children'=>array('file','folder'),
                'icon'=>array(
                    'image'=>'http://static.jstree.com/v.1.0rc/_docs/_drive.png',
                ),
            ),
        ),
    ),
    'hotkeys'=>array(
        "insert" => 'js:function (e) {
            var position = "inside";
            var context = this.data.ui.last_selected;
            if (this.data.ui.last_selected.attr("rel") == "file") {
                position = "after";
                context = this.data.ui.last_selected.parent();
            }
            var name = getUniqueTitle(context, "'.Yii::t('fileManager', 'New folder').'");
            this.create(this.data.ui.last_selected, position, { attr: { rel: "folder"}, data: name});
            e.stopImmediatePropagation();
            return false;
        }',
        "shift+insert" => 'js:function (e) {
            var position = "inside";
            var context = this.data.ui.last_selected;
            if (this.data.ui.last_selected.attr("rel") == "file") {
                position = "after";
                context = this.data.ui.last_selected.parent();
            }
            var name = getUniqueTitle(context, "'.Yii::t('fileManager', 'New file').'");
            this.create(this.data.ui.last_selected, position, { attr: { rel: "file"}, data: name});
            e.stopImmediatePropagation();
            return false;
        }',
        'ctrl+x' => 'js:function (e) {
            this.cut(this.data.ui.selected);
            e.stopImmediatePropagation();
            return false;
        }',
        'ctrl+c' => 'js:function (e) {
            this.copy(this.data.ui.selected);
            e.stopImmediatePropagation();
            return false;
        }',
        'ctrl+v' => 'js:function (e) {
            this.paste(this.data.ui.last_selected);
            e.stopImmediatePropagation();
            return false;
        }',
        'del' => 'js:function(e) {
            this.remove(this.data.ui.selected);
            e.stopImmediatePropagation();
            return false;
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
        'shift+up' => 'js:function (e) {
            var obj = this.data.ui.last_selected;
            var prev = this._get_prev(obj);
            var pselect = this.get_path(select_'.$id.');
            var plast = this.get_path(this.data.ui.selected.last());
            if (pselect.toString() != plast.toString()) {
                this.deselect_node(this.data.ui.selected.last());
            } else {
                if (prev)
                    this.select_node(prev);
            }
            e.stopImmediatePropagation();
            return false;
        }',
        'shift+down' => 'js:function (e) {
            var obj = this.data.ui.last_selected;
            var next = this._get_next(obj);
            var pselect = this.get_path(select_'.$id.');
            var pfirst = this.get_path(this.data.ui.selected.first());
            if (pselect.toString() != pfirst.toString()) {
                this.deselect_node(this.data.ui.selected.first());
            } else {
                if (next)
                    this.select_node(next);
            }
            e.stopImmediatePropagation();
            return false;
        }',
    ),
    'contextmenu'=>array(
        'select_node'=>true,
        'items' => array (
            'create' => array (
                'label' => Yii::t('fileManager', 'Create folder') . " (Ins)",
                'icon' => 'create',
                'action' => 'js:function (obj) { 
                    var position = "inside";
                    var context = obj;
                    if (obj.attr("rel") == "file") {
                        position = "after";
                        context = obj.parent();
                    }
                    var name = getUniqueTitle(context, "'.Yii::t('fileManager', 'New folder').'");
                    this.create(obj, position, { attr: { rel: "folder"}, data: name});  }',
            ),
            'create_file' => array (
                'label' => Yii::t('fileManager', 'Create file') . " (Shift+Ins)",
                'icon' => 'create',
                'action' => 'js:function (obj) { 
                    var position = "inside";
                    var context = obj;
                    if (obj.attr("rel") == "file") {
                        position = "after";
                        context = obj.parent();
                    }
                    var name = getUniqueTitle(context, "'.Yii::t('fileManager', 'New file').'");
                    this.create(obj, position, { attr: { rel: "file"}, data: name});  }',
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
                'action' => 'js:function (obj) { this.remove(this.data.ui.selected); }',
                'separator_after' => true,
            ),
            'ccp' => false,
            'cut' => array(
                'label' => Yii::t('cms', 'Cut') . ' (Ctrl+X)',
                'icon' => 'cut',
                'action' => 'js:function (obj) { this.cut(this.data.ui.selected); }',
            ),
            'copy' => array(
                'label' => Yii::t('cms', 'Copy') . ' (Ctrl+C)',
                'icon' => 'copy',
                'action' => 'js:function (obj) { this.copy(this.data.ui.selected); }',
            ),
            'paste' => array(
                'label' => Yii::t('cms', 'Paste') . ' (Ctrl+V)',
                'icon' => 'paste',
                'action' => 'js:function (obj) { this.paste(obj); }',
            )
        ),
    ),
    'events'=>array(
        'dblclick.jstree'=> 'js:function(e)'.<<<JS
 {
    $('#{$id}_filetree').jstree('toggle_node', e.target);
    e.stopImmediatePropagation();
    return false;
}
JS
,
        'select_node.jstree' => 'js:function(e)'.<<<JS
 {
    var select = $('#{$id}_filetree').jstree('get_selected');
    if (select.length == 1) {
        select_{$id} = select;
    }
}
JS
,
        'create_node.jstree' => 'js:function(e, data)'.<<<JS
 {
    var path = $('#{$id}_filetree').jstree('get_path', data.rslt.obj);
    var url = '/?r=fileManager/create&volume={$volume}&path='+pathToString(path)+'&type='+data.rslt.obj.attr('rel');
    var params = '';
    cmsAjaxSave(url, params, 'GET', function(html) {
        if (html.toString() != '0') {
            $('#{$id}_filetree').jstree('set_text', data.rslt.obj, html);
            $(data.rslt.obj).attr('rev', html);
        } else {
            $.jstree.rollback(data.rlbk);
        }
    });
}
JS
,
        'delete_node.jstree' => 'js:function(e, data)'.<<<JS
 {
    if (confirm("{$strDoYouReally}")) {
        var path = $('#{$id}_filetree').jstree('get_path', data.rslt.parent);
        if (!path) { path = new Array(); }
        path.push($('#{$id}_filetree').jstree('get_text', data.rslt.obj));
        var url = '/?r=fileManager/delete&volume={$volume}&path='+pathToString(path);
        var params = '';
        cmsAjaxSave(url, params, 'GET', function(html) {
            if (html.toString() == 'error') {
                $.jstree.rollback(data.rlbk);
            }
        });
    } else {
        $.jstree.rollback(data.rlbk);
    }
}
JS
,
        'rename_node.jstree' => 'js:function(e, data)'.<<<JS
 {

        var path = $('#{$id}_filetree').jstree('get_path', data.rslt.obj.parent());
        var newname = $('#{$id}_filetree').jstree('get_text', data.rslt.obj);
        var oldname = data.rslt.obj.attr('rev');
        if (!path) { path = new Array(); }
        path.push(oldname);
        var url = '/?r=fileManager/rename&volume={$volume}&path='+pathToString(path)+'&newName='+newname;
        var params = '';
        cmsAjaxSave(url, params, 'GET', function(html) {
            if (html.toString() != '0') {
                $('#{$id}_filetree').jstree('set_text', data.rslt.obj, html);
                $(data.rslt.obj).attr('rev', html);
            }
        });
}
JS
,
        'move_node.jstree' => 'js:function(e, data)'.<<<JS
 {
    var pageId = $(data.rslt.o).children('a:eq(0)').attr('rel');
    var parentId = $(data.rslt.o).parents('li:eq(0)').children('a:eq(0)').attr('rel');
    var siblings = [];
    $(data.rslt.o).parent().children().each(function () {
        siblings.push($(this).children('a:eq(0)').attr('rel'));
    });
//    var url = '/?r=page/sort&pageId='+pageId+'&language='+$.data(document.body, 'language');
//    var params = 'parentId='+parentId+'&'+decodeURIComponent($.param({'order': siblings}));
//    cmsAjaxSave(url, params, 'POST');
}
JS
,
    ),
));
?>
