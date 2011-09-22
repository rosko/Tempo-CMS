<?php $js = '';
$codemirrorPath=Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.vendors.codemirror'));
?>
<form id="<?=$id?>" method="POST" action="<?=$this->createAbsoluteUrl('filesEditor/save', array(
    'type' => $type,
    'name' => $name,
))?>">
<input type="hidden" name="file" value="" />
<input type="hidden" id="<?=$id?>_readonly" value="1" />

    <h2><?=$title?></h2> 
    <h3 id="<?=$id?>_currentfile"><?=Yii::t('filesEditor', 'File')?>: <span></span></h3>

    <?php if ($suggestions != array()) { ?>
    <?=Yii::t('filesEditor', 'Variables')?>: <?php
    $this->widget('ComboBox', array(
        'array' => $suggestions,
        'name' => $id . '_suggestions',
        'empty' => '',
        'showValues'=>true,
        'css'=>array(
            'width'=>'50%'
        ),
    ));
    ?>
    <br style="clear:both; "/>
    <?php } ?>
    
<textarea id="<?=$id?>_textarea" readonly="true" name="content" style="font-family:monospace;float:left;width:80%;margin-right:30px;" rows="20"></textarea>
<div>
    <ul class="files">
        <?php foreach ($files as $file) {?>
        <li><a href="#" class="fileitem" rev="<?=$file['name']?>" rel="<?=$file['writable']?>"><?=$file['title']?></a>
            <?php if (!$file['writable']) { ?>
        <nobr>[<?=Yii::t('filesEditor', 'read-only')?>]</nobr>
            <?php } ?>
        </li>
        <?php } ?>
    </ul>

    <a href="#" id="<?=$id?>_create"><?=Yii::t('filesEditor', 'Create file')?></a>
    <div id="<?=$id?>_creatediv" style="display:none;">
        <input type="text" id="<?=$id?>_newfilename" />
        <input type="button" id="<?=$id?>_createfile" value="Ok" />
    </div>
    <br /><br /><br />
    <a style="display:none;" id="<?=$id?>_delete" href="#" title="<?=Yii::t('filesEditor', 'Delete')?>">X</a><br />

</div>
<br style="clear:both; "/>
<input type="checkbox" id="<?=$id?>_highlight" checked="true" /><label for="<?=$id?>_highlight"><?=Yii::t('filesEditor', 'Code highlighting')?></label>
<br /><br />
<input disabled="true" type="submit" name="apply" value="<?=Yii::t('filesEditor', 'Save')?>" />
<input disabled="true" type="submit" name="save" value="<?=Yii::t('filesEditor', 'Save and close window')?>" />
</form>

<style type="text/css">
#<?=$id?>_newfilename {
    width:100px;
}
#<?=$id?>_delete {
    text-decoration:none;
    color:red;
    font-size:0.8em;
    font-weight:bold;
    border:1px solid red;
    padding:0px 0.3em;
    line-height:1em;
    border-radius:2em;
    font-family:Tahoma;
}
ul.files li a {
    padding:3px 10px;
}
ul.files li {
    padding:3px;
}
.CodeMirror-wrapping {
    border:1px solid black;
    float:left;
    margin-right:30px;
}
.ui-autocomplete {
		max-height: 300px;
		overflow-y: auto;
	}
/* IE 6 doesn't support max-height
 * we use height instead, but this forces the menu to always be this tall
 */
* html .ui-autocomplete {
    height: 300px;
}

</style>
<script src="<?=$codemirrorPath?>/js/codemirror.js" type="text/javascript"></script>
<script type="text/javascript">

var editor = null;
$('#<?=$id?>_textarea').data('initvalue',  $('#<?=$id?>_textarea').val());

function HighlightEnable(writable) {
    if ($('#<?=$id?>_highlight').attr('checked') && !editor) {
        editor = CodeMirror.fromTextArea('<?=$id?>_textarea', {
            height: $('#<?=$id?>_textarea').height()+'px',
            width: $('#<?=$id?>_textarea').width()+'px',
            parserfile: ["parsexml.js", "parsecss.js", "tokenizejavascript.js", "parsejavascript.js", "parsehtmlmixed.js"],
            stylesheet: ["<?=$codemirrorPath?>/css/xmlcolors.css", "<?=$codemirrorPath?>/css/jscolors.css", "<?=$codemirrorPath?>/css/csscolors.css"],
            path: "<?=$codemirrorPath?>/js/",
            continuousScanning: 100,
            enterMode: 'indent',
            tabMode: 'spaces',
            indentUnit: 4,
            readOnly: $('#<?=$id?>_textarea').attr('readonly')
        });
    }
}
function HighlightDisable()
{
    if (editor) {
        editor.toTextArea();
        editor = null;
    }
}

$('#<?=$id?> .fileitem').bind('click', function() {
    loadFile($(this).attr('rev'), $(this).attr('rel'), this);
    return false;
});

function isModified()
{
    if (editor)
        return ($('#<?=$id?>_textarea').data('initvalue') != editor.getCode());
    else
        return ($('#<?=$id?>_textarea').data('initvalue') != $('#<?=$id?>_textarea').val());
}

$('#<?=$id?>').parent().bind('dialogbeforeclose', function (event, ui) {
    if (isModified()) {
        $('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 10px 0;"></span><?=Yii::t('filesEditor', 'Save?')?></p>').dialog({
            resizable: false,
            title: '<?=Yii::t('filesEditor', 'File is modified')?>',
            modal: true,
            zIndex: 100000,
            buttons: {
                '<?=Yii::t('filesEditor', 'Yes')?>': function() {
                    $('#<?=$id?>').attr('rev','apply').submit();
                    if (editor)
                        $('#<?=$id?>_textarea').data('initvalue', editor.getCode());
                    else
                        $('#<?=$id?>_textarea').data('initvalue', $('#<?=$id?>_textarea').val());
                    $('#<?=$id?>').parent().dialog('close');
                    $( this ).dialog( "close" );
                },
                '<?=Yii::t('filesEditor', 'No')?>': function() {
                    if (editor)
                        $('#<?=$id?>_textarea').data('initvalue', editor.getCode());
                    else
                        $('#<?=$id?>_textarea').data('initvalue', $('#<?=$id?>_textarea').val());
                    $('#<?=$id?>').parent().dialog('close');
                    $( this ).dialog( "close" );
                },
                '<?=Yii::t('filesEditor', 'Cancel')?>': function() {
                    $( this ).dialog( "close" );
                }
            },
            close: function() {
                $(this).remove();
            }
        });
        return false;
     } else {
        return true;
     }
});

function loadFile(file, writable, button)
{
    if (isModified()) {
        $('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 10px 0;"></span><?=Yii::t('filesEditor', 'Save?')?></p>').dialog({
            resizable: false,
            title: '<?=Yii::t('filesEditor', 'File is modified')?>',
            modal: true,
            zIndex: 100000,
            buttons: {
                '<?=Yii::t('filesEditor', 'Yes')?>': function() {
                    $('#<?=$id?>').attr('rev','apply').submit();
                    if (editor)
                        $('#<?=$id?>_textarea').data('initvalue', editor.getCode());
                    else
                        $('#<?=$id?>_textarea').data('initvalue', $('#<?=$id?>_textarea').val());
                    loadFile(file, writable, button);
                    $( this ).dialog( "close" );
                },
                '<?=Yii::t('filesEditor', 'No')?>': function() {
                    if (editor)
                        $('#<?=$id?>_textarea').data('initvalue', editor.getCode());
                    else
                        $('#<?=$id?>_textarea').data('initvalue', $('#<?=$id?>_textarea').val());
                    loadFile(file, writable, button);
                    $( this ).dialog( "close" );
                },
                '<?=Yii::t('filesEditor', 'Cancel')?>': function() {
                    $( this ).dialog( "close" );
                }
            },
            close: function() {
                $(this).remove();
            }
        });
    } else {

        HighlightDisable();
        $('#<?=$id?>_currentfile span').html($(button).parents('li:eq(0)').text());
        $(button).css({'font-weight': 'bold'}).addClass('hover')
            .parents('li:eq(0)').siblings().find('a').css('font-weight','normal').removeClass('hover');
        $('#<?=$id?>_readonly').val(!writable);
        $('#<?=$id?>_textarea').attr('readonly', !writable);
        $('#<?=$id?> input[type=submit]').attr('disabled', !writable);
        if (writable) {
            $('#<?=$id?>_delete').appendTo($(button).parent()).show();
        } else {
            $('#<?=$id?>_delete').appendTo('#<?=$id?>').hide();
        }
        $.ajax({
            url: '/?r=filesEditor/load',
            data: 'type=<?=$type?>&name=<?=$name?>&file='+file,
            type: 'GET',
            cache: false,
            dataType: 'text',
            beforeSend: function() {
                cmsShowInfoPanel(cms_html_loading_image, 0);
            },
            success: function(html) {
                cmsHideInfoPanel();
                $('#<?=$id?> input[name=file]:hidden').val(file);
                $('#<?=$id?>_textarea').val(html);
                $('#<?=$id?>_textarea').data('initvalue',  $('#<?=$id?>_textarea').val());
                HighlightEnable();
            }
        });
    }
}

$('#<?=$id?>').submit(function() {
    $('#<?=$id?>').parent().unbind('dialogbeforeclose');
    if ($('#<?=$id?>_highlight').attr('checked'))
        $('#<?=$id?>_textarea').val(editor.getCode());
});
$('#<?=$id?> ul.files li:eq(<?=$default?>) a').click();

$('#<?=$id?>_highlight').click(function() {
    if ($(this).attr('checked')) {
        HighlightEnable();
    } else {
        HighlightDisable();
    }
});

$.fn.extend({
    insertAtCaret: function(myValue){

    thi = $(this).get(0);
    if (document.selection) {
        thi.focus();
        sel = document.selection.createRange();
        sel.text = myValue;
        thi.focus();
    }
      else if (thi.selectionStart || thi.selectionStart == '0') {
        var startPos = thi.selectionStart;
        var endPos = thi.selectionEnd;
        var scrollTop = thi.scrollTop;
        thi.value = thi.value.substring(0, startPos)+myValue+thi.value.substring(endPos,thi.value.length);
        thi.focus();
        thi.selectionStart = startPos + myValue.length;
        thi.selectionEnd = startPos + myValue.length;
        thi.scrollTop = scrollTop;
      } else {
        thi.value += myValue;
        thi.focus();
      }
    }
});

$('#<?=$id?>_suggestions').next('input').bind('autocompleteselect', function(event, ui) {
    if (editor)
        editor.replaceSelection(ui.item.value);
    else {
        if (!$('#<?=$id?>_readonly').val())
            $('#<?=$id?>_textarea').insertAtCaret(ui.item.value);
    }
});
$('#<?=$id?>_suggestions').next('input').keypress(function(e) {
    if (e.which == 13) {
        return false;
    }
});

$('#<?=$id?>_create').click(function() {
    $('#<?=$id?>_creatediv').slideToggle();
    $('#<?=$id?>_newfilename').focus();
    return false;
});
$('#<?=$id?>_newfilename').keypress(function(e) {
    if (e.which == 13) {
        $('#<?=$id?>_createfile').click();
        return false;
    }
    $('#<?=$id?>_newfilename').val($('#<?=$id?>_newfilename').val().replace(/[^0-9A-Za-z]*/gi, '').toLowerCase());
});

$('#<?=$id?>_createfile').click(function() {
    $('#<?=$id?>_newfilename').val($('#<?=$id?>_newfilename').val().replace(/[^0-9A-Za-z]*/gi, '').toLowerCase());
    var filename = $('#<?=$id?>_newfilename').val();
    if (filename != '') {
        $.ajax({
            url: '/?r=filesEditor/create',
            data: 'type=<?=$type?>&name=<?=$name?>&file='+filename,
            type: 'GET',
            cache: false,
            dataType: 'text',
            beforeSend: function() {
                cmsShowInfoPanel(cms_html_loading_image, 0);
            },
            success: function(html) {
                cmsHideInfoPanel();
                if (html) {
                    $('#<?=$id?> ul.files').append('<li><a href="#" class="fileitem" rev="'+filename+'" rel="1">'+filename+'</a></li>');
                    $('#<?=$id?> ul.files a[rev='+filename+']').bind('click', function() {
                        loadFile($(this).attr('rev'), $(this).attr('rel'), this);
                    }).click();
                }
            }
        });
    }
    return false;
});

$('#<?=$id?>_delete').click(function() {
    if (confirm('<?=Yii::t('filesEditor', 'Are you really want to delete file? Deleted files are unrecovered.')?>')) {
        var filename = $('#<?=$id?> input[name=file]:hidden').val();
        $.ajax({
            url: '/?r=filesEditor/delete',
            data: 'type=<?=$type?>&name=<?=$name?>&file='+filename,
            type: 'GET',
            cache: false,
            dataType: 'text',
            beforeSend: function() {
                cmsShowInfoPanel(cms_html_loading_image, 0);
            },
            success: function(html) {
                cmsHideInfoPanel();
                $('#<?=$id?>_delete').appendTo('#<?=$id?>').hide();
                $('#<?=$id?> ul.files li a.hover').parents('li:eq(0)').remove();
                $('#<?=$id?> ul.files a[rev=]').click();
            }
        });
        
    }
    return false;
});

<?=$js?>

</script>
