{if $content.url}
    <a href="{$content.url}"
       {if $unit.title} title="{$unit.title}" {/if}
       {if $content.target} target="{$content.target}" {/if}>
{/if}

<img src="{$image}" width="{$content.width}" height="{$content.height}"
     {if $unit.title} alt="{$unit.title}" {/if} />

{if $content.url}
    </a>
{/if}