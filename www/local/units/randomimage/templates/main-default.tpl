{if $content.url}
    <a href="{$content.url}"
       {if $caption} title="{$caption}" {/if}
       {if $content.target} target="{$content.target}" {/if}>
{/if}

<img src="{$image.filename}" width="{$content.width}" height="{$content.height}"
        {if $caption} alt="{$caption}" {/if} />

{if $content.url}
    </a>
{/if}