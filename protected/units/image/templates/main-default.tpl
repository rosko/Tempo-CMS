{if $content.url}
    <a href="{$content.url}"
       {if $widget.title} title="{$widget.title}" {/if}
       {if $content.target} target="{$content.target}" {/if}>
{/if}

<img src="{$content.image}" width="{$content.width}" height="{$content.height}"
     {if $widget.title} alt="{$widget.title}" {/if} />

{if $content.url}
    </a>
{/if}