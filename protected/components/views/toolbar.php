<?php $css = ''; $js = ''; ?>

<div class="cms-panel" id="<?=$id?>" style="background-color:<?=$config['panelBackgroundColor']?>; display:none;
            border:1px solid <?=$config['panelBorderColor'];?>;
            opacity:<?=$config['opacity'];?>;
            position:absolute;
            border-radius:<?=$config['borderRadius'];?>px;
            cursor:move;
            overflow:hidden;
            ">
    <div style="padding:<?=ceil($width/10)?>px <?=ceil($height/10)?>px;">

    <ul style="margin:0px;padding:0px;clear:both;">
<?php if (is_array($config['buttons'])) {

if ($config['vertical']) {
    $nl = $config['rows'];
} else {
    $nl = ceil(count($config['buttons']) / $config['rows']);
}
$i=0;

    foreach ($config['buttons'] as $name => $button) { ?>

<?php
$i++;
$url = Yii::app()->appearance->getIconUrlByAlias($button['icon'], '', $config['iconSet'], $config['iconSize']);
$hover = Yii::app()->appearance->getIconUrlByAlias($button['icon'], 'hover', $config['iconSet'], $config['iconSize']);
$_tmp = ceil($width/10);
if ($_tmp < 6) $_tmp = 6;
$css .= <<<EOD
    #{$id}_{$name} {
        background:url('{$url}') {$config['buttonBackgroundColor']} no-repeat center;
        width:{$width}px;
        height:{$height}px;
        display:block;
        cursor:pointer;
        padding:{$_tmp}px;
        border:1px solid transparent;
    }
    #{$id}_{$name}:hover {
        background-image:url('{$hover}');
        border:1px solid {$config['buttonBorderColor']};
    }
EOD;
if ($button['click'])
    $js .= "$('#{$id}_{$name}').click(".CJavaScript::encode($button['click']).");\n";

?>

        <li style="margin:<?=ceil($width/10)?>px;float:left;"><a id="<?=$id?>_<?=$name?>" title="<?=$button['title']?>" href="#"></a></li>
<?php
if ($nl == $i) {
    echo '</ul><ul style="margin:0px;padding:0px;clear:both;">';
    $i=0;
}
?>


<?php }
} ?>
    </ul>

<style type="text/css">
<?=$css?>
</style>
<script type="text/javascript">
jQuery(window).load(function() {

        <?=$js?>

<?php

list($p1, $p2, $p3) = $config['location']['position'];

if (is_numeric($p2) && is_numeric($p3)) {
    $l = $p2;
    $t = $p3;
} else {

if ($p1 == 'absolute') {
    $w = 'bw';
    $h = 'bh';
} else {
    $w = 'w';
    $h = 'h';
}

if ($p2 == 'right' || $p3 == 'right') {
    $l = $w.'-_w';
} else {
    $l = 0;
}
if ($p2 == 'bottom' || $p3 == 'bottom') {
    $t = $h.'-_h';
} else {
    $t = 0;
}
if ($p1 == 'outter') {
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
}

?>

        function location<?=$id;?>(hoster)
        {
            var bw = $(<?php echo ($config['location']['show'] == 'always' ? 'window' : "'body'");?>).width();
            var bh = $(<?php echo ($config['location']['show'] == 'always' ? 'window' : "'body'");?>).height();
            var w = $(hoster).width();
            var h = $(hoster).height();
            var p = $(hoster).offset();
            var l = p.left;
            var t = p.top;
            var _w = $('#<?=$id?>').width();
            var _h = $('#<?=$id?>').height();
            var _l = l+<?=$l?>;
            var _t = t+<?=$t?>;
            //alert('<?=$l?> - <?=$t?>');
            <?php if ($config['location']['save']) { ?>
                <?php if ($config['location']['draggable']) { ?>
                _t = $.cookie('<?=$id?>_top') ? $.cookie('<?=$id?>_top') : _t;
                _l = $.cookie('<?=$id?>_left') ? $.cookie('<?=$id?>_left') : _l;
                <?php } ?>
                <?php if ($config['location']['resizable']) { ?>
                _w = $.cookie('<?=$id?>_width') ? $.cookie('<?=$id?>_width') : _w;
                _h = $.cookie('<?=$id?>_height') ? $.cookie('<?=$id?>_height') : _h;
                <?php } ?>
            <?php } ?>
            if (_t > bh-_h) { _t = bh - _h; }
            if (_l > bw-_w) { _l = bw - _w; }
            if (_t < 0) { _t = 0; }
            if (_l < 0) { _l = 0; }

            $('#<?=$id?>').css({
                position:'<?php echo ($config['location']['show'] == 'always' ? 'fixed' : 'absolute');?>',
                left:_l+'px',
                top:_t+'px',
                width:_w+'px',
                height:_h+'px',
                'z-index': <?=$config['zIndex']?>
            });
            
        }

        location<?=$id?>('<?=$config['location']['selector']?>');

        $('#<?=$id?>')
        <?php if ($config['location']['show'] == 'always') { ?>
            .appendTo('<?=$config['location']['selector']?>').<?=$config['functionShow']?>
        <?php } ?>
        <?php if ($config['location']['draggable']) { ?>
            .draggable({
                stop: function(event, ui) {
                    <?php if ($config['location']['save']) { ?>
                        $.cookie('<?=$id?>_top', ui.position.top, { expires: 30, path: '/'});
                        $.cookie('<?=$id?>_left', ui.position.left, { expires: 30, path: '/'});
                    <?php } ?>
                }
            })
        <?php } ?>
        <?php if ($config['location']['resizable']) { ?>
            .resizable({
                stop: function(event, ui) {
                    <?php if ($config['location']['save']) { ?>
                        $.cookie('<?=$id?>_width', ui.size.width, { expires: 30, path: '/'});
                        $.cookie('<?=$id?>_height', ui.size.height, { expires: 30, path: '/'});
                    <?php } ?>
                }
            })
        <?php } ?>
;
        <?php if ($config['location']['show'] == 'hover') { ?>
            $('<?=$config['location']['selector']?>').live('mouseenter', function() {
                if (!$('<?=$config['location']['selector']?>').hasClass('ui-draggable-dragging')) {
                    location<?=$id?>(this);
                    $('#<?=$id?>').appendTo($(this)).<?=$config['functionShow']?>;
                } else {
                    $('#<?=$id?>').appendTo('body').<?=$config['functionHide']?>;
                }
            }).live('mouseleave', function() {
                $('#<?=$id?>').appendTo('body').<?=$config['functionHide']?>;
            });
        <?php } elseif ($config['location']['show'] == 'click') { ?>
            $('<?=$config['location']['selector']?>').live('click', function() {
                var obj = $('#<?=$id?>');
                if (obj.css('display') == 'none' && !$('<?=$config['location']['selector']?>').hasClass('ui-draggable-dragging')) {
                    location<?=$id?>(this);
                    obj.appendTo($(this)).<?=$config['functionShow']?>;
                } else {
                    obj.appendTo('body').<?=$config['functionHide']?>;
                }
            });
        <?php } ?>

});



</script>

    </div>

</div>