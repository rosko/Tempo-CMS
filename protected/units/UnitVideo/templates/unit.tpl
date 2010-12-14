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
{t text='Video is not available or entered link is not recognized. Many video sites (video hosting) provide the ability to insert videos on other web sites using HTML-code. Try this opportunity. The site that hosts the video, copy the HTML-code and insert this block in the appropriate field.'}
</p>

        
        {/if}

    {/if}
{/if}
