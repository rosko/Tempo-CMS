<script type="text/javascript">
<?php foreach ($content->resizableObjects() as $selector => $o) { ?>
    if ($('#cms-pageunit-<?=$pageUnit->id?>').find('<?=$selector?>').length) {
    $('#cms-pageunit-<?=$pageUnit->id?>').find('<?=$selector?>').each(function(){
        if ($(this).width() == 0)
            $(this).width(10);
        if ($(this).height() == 0)
            $(this).height(10);
    });
    $('#cms-pageunit-<?=$pageUnit->id?>').find('<?=$selector?>').resizable({
        aspectRatio: false,
        handles: 'n, e, s, w, ne, se, sw, nw',
        start: function(event, ui) {
            var s = $(event.target).children('<?=$selector?>');
            var o = $('<span></span>').css({
                'position':'absolute',
                'text-align':'center',
                'font-size':'20px',
                'color':'black',
                'text-decoration':'none',
                'border':'0px',
                'padding-top':Math.ceil(s.eq(0).width()/2-10)+'px'
            }).attr('id', 'infospan-<?=$pageUnit->id?>')
                .width(s.eq(0).width())
                .height(s.eq(0).height())
                .insertBefore(s);
            ui.helper.css({
                'top': '',
                'left': ''
            });
        },
        resize: function(event, ui) {
            var s = $(event.target).children('<?=$selector?>');
            var size = Math.round(ui.size.width) + ' x ' + Math.round(ui.size.height);
            var o = $('<span></span').css({
                'background':'white',
                'opacity':'0.6'
            })
                .html(size);
            $('#infospan-<?=$pageUnit->id?>')
                .width(s.eq(0).width())
                .html(o)
                .height(s.eq(0).height())
                .css('padding-top', Math.ceil(s.eq(0).height()/2-10)+'px');
            ui.helper.css({
                'top': '',
                'left': ''
            });
        },
        stop: function(event, ui) {
            $('#infospan-<?=$pageUnit->id?>').remove();
            <?php if (is_array($o['attributes'])) { ?>
            var data = 'ContentModel[<?=$o['attributes'][0]?>]='+Math.round(ui.size.width)+'&ContentModel[<?=$o['attributes'][1]?>]='+Math.round(ui.size.height);
            <?php } else { ?>
            var s = $(event.target).children('<?=$selector?>');
            var data = 'attribute=<?=$o['attributes']?>&width='+Math.round(ui.size.width)+'&height='+Math.round(ui.size.height)+'&tag='+s.get(0).tagName+'&number='+$('#cms-pageunit-<?=$pageUnit->id?>').find('<?=$selector?>').index(s.get(0));
            <?php } ?>
            ui.helper.css({
                'top': '',
                'left': ''
            });
            cmsAjaxSave('/?r=unit/ajax&unitId=<?=$unit->id?>', data, 'POST');
        }
    }).parent('.ui-wrapper').css({
        'position': 'relative',
        'display': 'inline-block',
        'top': '',
        'left': ''
    }).children('.ui-resizable-handle').css({
        'z-index':'1'
    });
    $('#cms-pageunit-<?=$pageUnit->id?>').find('<?=$selector?>').each(function(){
        if ($(this).attr('align'))
            $(this).parent('.ui-wrapper').css('float', $(this).attr('align'));
    });
    }
<?php } ?>
</script>
