<?php
$js = '';
?>

<div class="<?=$config['cssClass']?>" id="<?=$id?>">
<?php echo $this->renderButtons($config['buttons'], $config['vertical'], $config['rows'], $config, $id, $js); ?>
</div>


<?php
// JavaScript
if ($config['click'])
    $js .= "$('#{$id}').click(".CJavaScript::encode($config['click']).");\n";
if ($config['dblclick'])
    $js .= "$('#{$id}').dblclick(".CJavaScript::encode($config['dblclick']).");\n";


list($p1, $p2, $p3) = $config['location']['position'];
if (is_numeric($p2) && is_numeric($p3)) {
    $l = $p2;$t = $p3;
} else {
    $w = ($p1 == 'absolute') ? 'bw' : 'w';
    $h = ($p1 == 'absolute') ? 'bh' : 'h';

    $l = ($p2 == 'right' || $p3 == 'right') ? $w.'-_w' : 0;
    $t = ($p2 == 'bottom' || $p3 == 'bottom') ? $t = $h.'-_h' : 0;
    if ($p1 == 'outter')
        if ($p2 == 'left') {
            $l = '0-_w';
        } elseif ($p2 == 'right') {
            $l = $w;
        } elseif ($p2 == 'top') {
            $t = '0-_h';
        } elseif ($p2 == 'bottom') {
            $t = $h;
        }
}

if (($p2 == 'wide' && ($p3 == 'top' || $p3 == 'bottom')) ||
    ($p3 == 'wide' && ($p2 == 'top' || $p2 == 'bottom')) ){
    $js .= "$('#{$id}').css('width', '100%');";
}
if (($p2 == 'wide' && ($p3 == 'left' || $p3 == 'right')) ||
    ($p3 == 'wide' && ($p2 == 'left' || $p2 == 'right')) ){
    $js .= "$('#{$id}').css('height', '100%');";
}

$b = $config['location']['show'] == 'always' ? 'window' : "'body'";
$js .= <<<EOD
    function location{$id}(hoster)
    {
        if ($(hoster).length == 0) { return; }
        var bw = $({$b}).width();
        var bh = $({$b}).height();
        var w = $(hoster).width();
        var h = $(hoster).height();
        var p = $(hoster).position();
        var l = p.left;
        var t = p.top;
        var _w = $('#{$id}').width();
        var _h = $('#{$id}').height();
        var _l = l+{$l};
        var _t = t+{$t};
EOD;

if ($config['location']['save']) {
    if ($config['location']['draggable']) {
        $js .= <<<EOD
            _t = $.cookie('{$id}_top') ? $.cookie('{$id}_top') : _t;
            _l = $.cookie('{$id}_left') ? $.cookie('{$id}_left') : _l;
EOD;
    }
    if ($config['location']['resizable']) {
        $js .= <<<EOD
            _w = $.cookie('{$id}_width') ? $.cookie('{$id}_width') : _w;
            _h = $.cookie('{$id}_height') ? $.cookie('{$id}_height') : _h;
EOD;
    }
}

$p = $config['location']['show'] == 'always' ? 'fixed' : 'absolute';
$cursor = $config['location']['draggable'] ? 'move': 'auto';

$js .= <<<EOD
            if (_t > bh-_h) { _t = bh - _h; }
            if (_l > bw-_w) { _l = bw - _w; }
            if (_t < 0) { _t = 0; }
            if (_l < 0) { _l = 0; }

            $('#{$id}').css({
                position:'{$p}',
                left:_l+'px',
                top:_t+'px',
                width:_w+'px',
                height:_h+'px',
                cursor:'{$cursor}'
            });
        }

        location{$id}('{$config['location']['selector']}');

        $('#{$id}')
EOD;

if ($config['location']['show'] == 'always') {
    $js .= " .appendTo('{$config['location']['selector']}').{$config['functionShow']} ";
}

if ($config['location']['draggable']) {

    $js .= "
            .draggable({
                stop: function(event, ui) {";
                    if ($config['location']['save']) {
                        $js .= "
                        $.cookie('{$id}_top', ui.position.top, { expires: 30, path: '/'});
                        $.cookie('{$id}_left', ui.position.left, { expires: 30, path: '/'});
                        ";
                    }
    $js .= "
                }
            })";
}
if ($config['location']['resizable']) {
    $js .= "
            .resizable({
                stop: function(event, ui) {";
                    if ($config['location']['save']) {
                        $js .= "
                        $.cookie('{$id}_width', ui.size.width, { expires: 30, path: '/'});
                        $.cookie('{$id}_height', ui.size.height, { expires: 30, path: '/'});
                        ";
                    }
    $js .= "
                }
            })";
} 

$js .= ";";

if ($config['location']['show'] == 'hover') {

    $js .= <<<EOD
            $('{$config['location']['selector']}').live('mouseenter', function() {
                if (!$('{$config['location']['selector']}').hasClass('ui-draggable-dragging')) {
                    location{$id}(this);
                    $('#{$id}').appendTo($(this)).{$config['functionShow']};
                } else {
                    $('#{$id}').appendTo('body').{$config['functionHide']};
                }
            }).live('mouseleave', function() {
                $('#{$id}').appendTo('body').{$config['functionHide']};
            });
EOD;

} elseif ($config['location']['show'] == 'click') {
    $js .= <<<EOD
            $('{$config['location']['selector']}').live('click', function() {
                var obj = $('#{$id}');
                if (obj.css('display') == 'none' && !$('{$config['location']['selector']}').hasClass('ui-draggable-dragging')) {
                    location{$id}(this);
                    obj.appendTo($(this)).{$config['functionShow']};
                } else {
                    obj.appendTo('body').{$config['functionHide']};
                }
                return false;
            });
EOD;
}


$cs = Yii::app()->getClientScript();
$cs->registerCoreScript('jquery');
$cs->registerCoreScript('jquery.ui');
$cs->registerCssFile($config['cssFile']);
$cs->registerScript('Toolbar'.$id, $js, CClientScript::POS_READY);
