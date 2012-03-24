{$height = 55}

{if $content.url}
    <a href="{$content.url}"
       {if $widget.title} title="{$widget.title}" {/if}
       {if $content.target} target="{$content.target}" {/if}
       style="z-index:20;position:absolute;width:{$content.width}px;height:{$content.height}px;display:block;"
       >&nbsp;
    </a>
{/if}

<div style="position:absolute;z-index:15;margin-top:{$content.height-$height+5}px;margin-left:10px;">
    <span style="color:yellow;font-size:32px;">Almaz<span style="color:white;">GO</span>.com</span><br />
    <span style="color:white;">{if $language=='ru'}Следуя за Богом &mdash; идя к сиротам{else}Follow God &mdash; Go to orphans{/if}</span>
</div>
<div style="z-index:11;position:absolute;background-color:black;opacity:0.45;margin-top:{$content.height-$height}px;width:{$content.width}px;height:{$height}px;">&nbsp;</div>

<img src="{$image}" width="{$content.width}" height="{$content.height}"
     {if $widget.title} alt="{$widget.title}" {/if} />

{if $content.url}
{/if}

