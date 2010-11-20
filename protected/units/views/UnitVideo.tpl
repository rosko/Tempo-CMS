{if $video !== false}

    {$video}

    {if $content.show_link}
        <p><a target="_blank" href="{$content.video}">{if $unit.title}{$unit.title}{else}{$content.video}{/if}</a></p>
    {/if}

{else}

    {if $content.html != ''}

        {$content.html}

    {else}
    
        {if $editMode}
        
<p>
Видео отсутствует или ссылка нераспознана. Многие видеосайты (видеохостинги)
предоставляют возможность вставки видео на другие сайты с помощью HTML-кода.
Попробуйте эту возможность. На сайте, где размещено видео, скопируйте HTML-код
и вставьте в этот блок в соответствующее поле.
</p>

        
        {/if}

    {/if}
{/if}
