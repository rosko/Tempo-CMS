<script type="text/javascript">
<?php foreach ($content->resizableObjects() as $selector => $o) { ?>
    $('#cms-pageunit-<?=$pageunit->id?>').find('<?=$selector?>').each(function(){
        if ($(this).width() == 0)
            $(this).width(10);
        if ($(this).height() == 0)
            $(this).height(10);
    });
    $('#cms-pageunit-<?=$pageunit->id?>').find('<?=$selector?>').resizable({
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
            }).attr('id', 'infospan-<?=$pageunit->id?>')
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
            $('#infospan-<?=$pageunit->id?>')
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
            $('#infospan-<?=$pageunit->id?>').remove();
            <?php if (is_array($o['attributes'])) { ?>
            var data = 'Content[<?=$o['attributes'][0]?>]='+Math.round(ui.size.width)+'&Content[<?=$o['attributes'][1]?>]='+Math.round(ui.size.height);
            <?php } else { ?>
            var s = $(event.target).children('<?=$selector?>');
            var data = 'attribute=<?=$o['attributes']?>&width='+Math.round(ui.size.width)+'&height='+Math.round(ui.size.height)+'&tag='+s.get(0).tagName+'&number='+$('#cms-pageunit-<?=$pageunit->id?>').find('<?=$selector?>').index(s.get(0));
            <?php } ?>
            ui.helper.css({
                'top': '',
                'left': ''
            });
            ajaxSave('/?r=page/unitAjax&unit_id=<?=$unit->id?>', data, 'POST');
        }
    }).parent('.ui-wrapper').css({
        'position': 'relative',
        'display': 'inline-block',
        'top': '',
        'left': ''
    });
    $('#cms-pageunit-<?=$pageunit->id?>').find('<?=$selector?>').each(function(){
        if ($(this).attr('align'))
            $(this).parent('.ui-wrapper').css('float', $(this).attr('align'));
    });
<?php } ?>
</script>
