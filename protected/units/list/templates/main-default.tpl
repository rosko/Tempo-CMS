{if $widget.title}
<h3>{$widget.title}</h3>
{/if}

{if count($items)}
<dl>
    {foreach $items as $item}
        <dt><a href="{$item.link}">{$item.title}</a></dt>
        <dd>
            <p><small>{$item.updated|date_format:"d F Y"}</small><br />
            {$link = " <a href='{$item.link}'>&hellip;</a>"}
            {$item.short|strip_tags|truncate:20:''} {$item.short|strip_tags|truncate:20:$link}</p>
        </dd>
    {/foreach}
</dl>
{else}

    <h2>{t text='empty'}</h2>

{/if}
