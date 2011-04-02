<?php
$css = ''; $js = '';
$vmargin = max(ceil($width/10), 7);
$hmargin = max(ceil($height/10), 7);

?>

<div class="cms-panel" id="<?=$id?>"><div style="padding:<?=$hmargin?>px <?=$vmargin?>px;">
    <ul>
<?php if (is_array($config['buttons'])) {
    $nl = $config['vertical'] ? $config['rows'] : ceil(count($config['buttons']) / $config['rows']);
    $i=0;
?>

    <?php foreach ($config['buttons'] as $name => $button) { ?>

<?php
if ($button==null) continue;

$i++;
$url = Toolbar::getIconUrlByAlias($button['icon'], '', $config['iconSet'], $config['iconSize']);
$hover = Toolbar::getIconUrlByAlias($button['icon'], 'hover', $config['iconSet'], $config['iconSize']);
if (!$hover) { $hover = $url; }

$bgcolor = ($hover == $url) ? 'background-color: '. $config['buttonBorderColor'] . ';' : '';
$css .= <<<EOD

    #{$id}_{$name} {
        background:url('{$url}') {$config['buttonBackgroundColor']} no-repeat center;
        width:{$width}px;
        height:{$height}px;
        display:block;
        cursor:pointer;
        padding:{$hmargin}px;
        border:1px solid transparent;
    }
    #{$id}_{$name}:hover {
        background-image:url('{$hover}');
        {$bgcolor}
        border:1px solid {$config['buttonBorderColor']};
    }

EOD;

if ($button['click'])
    $js .= "$('#{$id}_{$name}').click(".CJavaScript::encode($button['click']).");\n";
?>

        <li style="margin:<?=$hmargin?>px <?=$wmargin?>px;float:left;"><a id="<?=$id?>_<?=$name?>" title="<?=$button['title']?>" href="#"></a></li>

<?php if ($nl == $i) { ?>
    </ul><ul>
<?php $i=0; }?>


    <?php } ?>
<?php } ?>

    </ul>
</div></div>


<?php
// CSS

$css .= <<<EOD

#{$id} {
    display:none;
    background-color:{$config['panelBackgroundColor']};
    border:1px solid {$config['panelBorderColor']};
    opacity:{$config['opacity']};
    position:absolute;
    border-radius:{$config['borderRadius']}px;
    cursor:move;
    overflow:hidden;
}
#{$id} ul {
    margin:0px;padding:0px;clear:both;list-style-type:none;
}

EOD;

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
    $h = ($p1 == 'absolute') ? 'hw' : 'h';

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


$b = $config['location']['show'] == 'always' ? 'window' : "'body'";
$js .= <<<EOD
    function location{$id}(hoster)
    {
        if ($(hoster).length == 0) { return; }
        var bw = $({$b}).width();
        var bh = $({$b}).height();
        var w = $(hoster).width();
        var h = $(hoster).height();
        var p = $(hoster).offset();
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
                'z-index': {$config['zIndex']}
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
            });
EOD;
}


$cs = Yii::app()->getClientScript();
$cs->registerCoreScript('jquery');
$cs->registerCoreScript('jquery.ui');
$cs->registerCss('Toolbar'.$id, $css);
$cs->registerScript('Toolbar'.$id, $js, CClientScript::POS_READY);

?>
